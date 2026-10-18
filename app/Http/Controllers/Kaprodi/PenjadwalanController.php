<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\Penjadwalan;
use App\Models\SchedulePeriod;
use App\Models\User;
use App\Services\GeneticAlgorithm\ScheduleOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PenjadwalanController extends Controller
{
    public function index(Request $request)
    {
        $period = SchedulePeriod::find($request->integer('period_id'));

        return Inertia::render('Kaprodi/Penjadwalan', [
            'periods' => SchedulePeriod::orderBy('start_date')->get(),
            'selectedPeriodId' => $period?->id,
            'penjadwalanList' => Penjadwalan::with(['mahasiswa:id,name,nim_nip,username', 'pembimbing1:id,name', 'pembimbing2:id,name', 'penguji1:id,name', 'penguji2:id,name'])
                ->when($period, fn ($query) => $query->where('period_id', $period->id))->orderBy('tanggal')->get(),
            'dosenOptions' => User::where('role', 'dosen')->get(['id', 'name', 'nim_nip', 'prodi', 'jabatan']),
            'capabilities' => ['lecturerKbk' => false, 'availability' => false, 'lecturerQuota' => false],
        ]);
    }

    private function registrationClass(string $type): string
    {
        return $type === 'sempro' ? PendaftaranSempro::class : PendaftaranSidang::class;
    }

    private function candidate($registration, string $type): ?array
    {
        $title = PengajuanJudul::where('mahasiswa_id', $registration->mahasiswa_id)->where('status', 'disetujui')->latest('id')->first();
        if (! $title || ! $title->pembimbing_1_id) {
            return null;
        }
        $supervisors = array_values(array_unique(array_filter([$title->pembimbing_1_id, $title->pembimbing_2_id])));
        if (User::whereIn('id', $supervisors)->where('role', 'dosen')->count() !== count($supervisors)) {
            return null;
        }
        $student = User::where('id', $registration->mahasiswa_id)->where('role', 'mahasiswa')->first();
        if (! $student) {
            return null;
        }

        return ['pendaftaran_id' => $registration->id, 'mahasiswa_id' => $student->id, 'tipe' => $type, 'nama' => $student->name, 'nim' => $student->nim_nip ?? $student->username, 'judul' => $registration->judul_ta, 'kbk' => $title->bidang_penelitian, 'supervisor_ids' => $supervisors, 'pembimbing_1_id' => $title->pembimbing_1_id, 'pembimbing_2_id' => $title->pembimbing_2_id];
    }

    private function bookings(): array
    {
        return Penjadwalan::whereIn('status', ['draft', 'terpublikasi', 'selesai'])->lockForUpdate()->get()->map(fn ($row) => array_merge($row->toArray(), ['tanggal' => $row->tanggal->format('Y-m-d')]))->all();
    }

    public function generateGa(Request $request)
    {
        $data = $request->validate(['period_id' => ['required', 'integer', function ($attribute, $value, $fail) {
            if (is_bool($value)) {
                $fail('The period id must be an integer.');
            }
        }, 'exists:schedule_periods,id'], 'popSize' => ['nullable', 'integer', 'min:10', 'max:200'], 'maxGenerations' => ['nullable', 'integer', 'min:10', 'max:500'], 'crossoverRate' => ['nullable', 'numeric', 'between:0,1'], 'mutationRate' => ['nullable', 'numeric', 'between:0,1']]);
        $period = SchedulePeriod::findOrFail($data['period_id']);
        if (! $period->is_open) {
            throw ValidationException::withMessages(['period_id' => 'Periode ditutup.']);
        }
        $class = $this->registrationClass($period->tipe);
        $candidates = [];
        $excluded = [];
        $scheduledIds = Penjadwalan::where('tipe', $period->tipe)
            ->where('status', '!=', 'dibatalkan')->pluck('pendaftaran_id')->flip();
        foreach ($class::where('status', 'verifikasi_tendik')->where('status_akademik', 'disetujui')->where('gelombang', $period->gelombang)->orderBy('id')->get() as $registration) {
            if ($scheduledIds->has($registration->id)) {
                continue;
            }
            $candidate = $this->candidate($registration, $period->tipe);
            if ($candidate) {
                $candidates[] = $candidate;
            } else {
                $excluded[] = ['pendaftaran_id' => $registration->id, 'tipe' => $period->tipe, 'reason' => 'Judul/supervisor/student belum valid.'];
            }
        }
        $bookings = $this->bookings();
        $used = Penjadwalan::where('period_id', $period->id)->where('status', '!=', 'dibatalkan')->count();
        $capacity = max(0, $period->capacity - $used);
        $overflow = array_slice($candidates, $capacity);
        $candidates = array_slice($candidates, 0, $capacity);
        $lecturers = User::where('role', 'dosen')->get(['id', 'name', 'prodi', 'jabatan'])->map(function ($lecturer) use ($bookings) {
            $row = $lecturer->toArray();
            $row['workload'] = count(array_filter($bookings, fn ($b) => in_array($lecturer->id, [$b['penguji_1_id'], $b['penguji_2_id']])));

            return $row;
        })->all();
        $result = (new ScheduleOptimizer($data['popSize'] ?? 50, $data['maxGenerations'] ?? 100, $data['crossoverRate'] ?? .8, $data['mutationRate'] ?? .03))->optimize($candidates, $lecturers, $period->slots, $bookings);
        $result['unscheduled'] = array_merge($result['unscheduled'], $overflow, $excluded);
        if ($result['unscheduled']) {
            $result['outcome'] = $result['schedule'] ? 'partial' : 'infeasible';
            $result['success'] = false;
        }
        $result['period_id'] = $period->id;
        $result['limitations'] = ['KBK dosen, kuota dosen, dan kalender ketersediaan belum tersimpan; tidak diasumsikan sebagai data nyata. Konflik booking dan kapasitas periode tetap diperiksa.'];

        return response()->json($result);
    }

    public function storePeriod(Request $request, ?int $id = null)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:150'], 'tipe' => ['required', 'in:sempro,sidang'], 'gelombang' => ['required', 'integer', 'min:1'], 'capacity' => ['required', 'integer', 'min:1', 'max:1000'], 'start_date' => ['required', 'date_format:Y-m-d'], 'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'], 'is_open' => ['required', 'boolean'], 'slots' => ['required', 'array', 'min:1', 'max:100'], 'slots.*.tanggal' => ['required', 'date_format:Y-m-d'], 'slots.*.jam_mulai' => ['required', 'date_format:H:i:s'], 'slots.*.jam_selesai' => ['required', 'date_format:H:i:s']]);
        foreach ($data['slots'] as $slot) {
            if ($slot['tanggal'] < $data['start_date'] || $slot['tanggal'] > $data['end_date'] || $slot['jam_mulai'] >= $slot['jam_selesai']) {
                throw ValidationException::withMessages(['slots' => 'Slot harus di dalam periode dengan waktu selesai sesudah mulai.']);
            }
        }
        DB::transaction(function () use ($id, $data) {
            $period = $id ? SchedulePeriod::lockForUpdate()->findOrFail($id) : new SchedulePeriod;
            $period->fill($data);
            if ($id && Penjadwalan::where('period_id', $id)->exists() && $period->isDirty(['name', 'tipe', 'gelombang', 'capacity', 'start_date', 'end_date', 'slots'])) {
                throw ValidationException::withMessages(['slots' => 'Definisi periode dengan jadwal tersimpan tidak dapat diubah.']);
            }
            if ($period->is_open && (! $period->exists || $period->isDirty('is_open')) && SchedulePeriod::where('tipe', $period->tipe)->where('is_open', true)->when($id, fn ($query) => $query->where('id', '!=', $id))->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['is_open' => 'Hanya satu periode per jenis dapat dibuka. Tutup periode lain terlebih dahulu.']);
            }
            $period->save();
        });

        return back()->with('success', 'Periode tersimpan.');
    }

    public function simpan(Request $request)
    {
        $data = $request->validate(['period_id' => ['required', 'integer', function ($attribute, $value, $fail) {
            if (is_bool($value)) {
                $fail('The period id must be an integer.');
            }
        }, 'exists:schedule_periods,id'], 'schedules' => ['required', 'array', 'min:1', 'max:1000'], 'schedules.*.pendaftaran_id' => ['required', 'integer'], 'schedules.*.tipe' => ['required', 'in:sempro,sidang'], 'schedules.*.mahasiswa_id' => ['required', 'integer'], 'schedules.*.penguji_1_id' => ['required', 'integer'], 'schedules.*.penguji_2_id' => ['required', 'integer'], 'schedules.*.tanggal' => ['required', 'date_format:Y-m-d'], 'schedules.*.jam_mulai' => ['required', 'date_format:H:i:s'], 'schedules.*.jam_selesai' => ['required', 'date_format:H:i:s']]);
        $this->publish($data['period_id'], $data['schedules'], $request->user()->id);

        return back()->with('success', 'Jadwal tersimpan dan tervalidasi.');
    }

    private function publish(int $periodId, array $rows, int $actor, ?int $overrideId = null): void
    {
        DB::transaction(function () use ($periodId, $rows, $actor, $overrideId) {
            // Serialize publication globally through lecturer locks, then period/registration locks.
            User::where('role', 'dosen')->orderBy('id')->lockForUpdate()->get(['id']);
            $period = SchedulePeriod::lockForUpdate()->findOrFail($periodId);
            if (! $period->is_open) {
                throw ValidationException::withMessages(['schedules' => 'Periode ditutup.']);
            }
            $bookings = $this->bookings();
            $seen = [];
            foreach ($rows as $input) {
                $key = $input['tipe'].':'.$input['pendaftaran_id'];
                if (isset($seen[$key]) || $input['tipe'] !== $period->tipe) {
                    throw ValidationException::withMessages(['schedules' => 'Jenis/duplikat pendaftaran tidak valid.']);
                }$seen[$key] = true;
                $class = $this->registrationClass($input['tipe']);
                $registration = $class::lockForUpdate()->find($input['pendaftaran_id']);
                $existing = Penjadwalan::where('tipe', $input['tipe'])->where('pendaftaran_id', $input['pendaftaran_id'])->lockForUpdate()->first();
                if (! $registration || $registration->mahasiswa_id !== $input['mahasiswa_id'] || $registration->status_akademik !== 'disetujui' || ! in_array($registration->status, ['verifikasi_tendik', 'terjadwal'], true) || ($registration->status === 'terjadwal' && ! $existing) || $registration->gelombang !== $period->gelombang) {
                    throw ValidationException::withMessages(['schedules' => 'Pendaftaran belum siap/tidak cocok.']);
                }
                if ($existing && ($existing->period_id !== $periodId || $existing->status === 'selesai' || $existing->penilaian()->exists() || ($overrideId && $existing->id !== $overrideId))) {
                    throw ValidationException::withMessages(['schedules' => 'Jadwal sudah di periode lain/selesai.']);
                }
                $candidate = $this->candidate($registration, $input['tipe']);
                if (! $candidate) {
                    throw ValidationException::withMessages(['schedules' => 'Supervisor belum valid.']);
                }
                $row = array_merge($input, $candidate, ['period_id' => $periodId, 'status' => 'terpublikasi', 'gelombang' => $period->gelombang, 'dibuat_oleh' => $actor]);
                if (User::whereIn('id', [$row['penguji_1_id'], $row['penguji_2_id']])->where('role', 'dosen')->count() !== 2) {
                    throw ValidationException::withMessages(['schedules' => 'Dua penguji dosen berbeda wajib dipilih.']);
                }
                $slot = ['tanggal' => $row['tanggal'], 'jam_mulai' => $row['jam_mulai'], 'jam_selesai' => $row['jam_selesai']];
                if (! in_array($slot, $period->slots)) {
                    throw ValidationException::withMessages(['schedules' => 'Waktu tidak sesuai slot periode.']);
                }
                $otherBookings = array_values(array_filter($bookings, fn ($b) => ! $existing || $b['id'] !== $existing->id));
                if ((new ScheduleOptimizer)->conflicts($row, $otherBookings)) {
                    throw ValidationException::withMessages(['schedules' => 'Konflik penguji/supervisor atau interval booking.']);
                }
                if ((! $existing || $existing->status === 'dibatalkan') && Penjadwalan::where('period_id', $periodId)->where('status', '!=', 'dibatalkan')->count() >= $period->capacity) {
                    throw ValidationException::withMessages(['schedules' => 'Kapasitas periode habis.']);
                }
                $schedule = $existing ?? new Penjadwalan;
                $schedule->fill($row)->save();
                $registration->update(['status' => 'terjadwal']);
                $bookings = $this->bookings();
            }
        });
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate(['penguji_1_id' => ['sometimes', 'required', 'integer'], 'penguji_2_id' => ['sometimes', 'required', 'integer'], 'tanggal' => ['sometimes', 'required', 'date_format:Y-m-d'], 'jam_mulai' => ['sometimes', 'required', 'date_format:H:i:s'], 'jam_selesai' => ['sometimes', 'required', 'date_format:H:i:s']]);
        $schedule = Penjadwalan::findOrFail($id);
        if (! $schedule->period_id || $schedule->penilaian()->exists()) {
            throw ValidationException::withMessages(['schedules' => 'Jadwal historis/sudah dinilai tidak dapat diubah.']);
        }
        $row = array_merge($schedule->only(['pendaftaran_id', 'mahasiswa_id', 'tipe', 'penguji_1_id', 'penguji_2_id', 'jam_mulai', 'jam_selesai']), ['tanggal' => $schedule->tanggal->format('Y-m-d')], $data);
        $this->publish($schedule->period_id, [$row], $request->user()->id, $id);

        return back()->with('success', 'Penyesuaian tersimpan.');
    }
}
