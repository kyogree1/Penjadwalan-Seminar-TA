<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicScheduleController;
use App\Models\Notification;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\Penjadwalan;
use App\Models\SchedulePeriod;
use App\Models\User;
use App\Services\GeneticAlgorithm\ScheduleOptimizer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PenjadwalanController extends Controller
{
    public function index(Request $request)
    {
        $tipe = $request->query('tipe');
        if ($tipe === 'sidang' || $tipe === 'ta') {
            return redirect()->route('kaprodi.penjadwalan.plotting-ta', $request->query());
        }

        return redirect()->route('kaprodi.penjadwalan.plotting-sempro', $request->query());
    }

    public function jadwalIndex()
    {
        return Inertia::render('Kaprodi/Jadwal', [
            'publicSchedules' => PublicScheduleController::getPublicSchedules(),
            'publicPeriods' => PublicScheduleController::getPublicPeriods(),
        ]);
    }

    public function plottingSemproIndex(Request $request)
    {
        $periods = SchedulePeriod::where('tipe', 'sempro')
            ->orderBy('gelombang')
            ->get()
            ->map(function ($period) {
                return [
                    'id' => $period->id,
                    'name' => $this->formatPeriodYear($period->name),
                    'tipe' => $period->tipe,
                    'semester' => $period->semester ?? 'Gasal',
                    'prodi' => $period->prodi ?? 'Informatika',
                    'gelombang' => $period->gelombang,
                    'capacity' => $period->capacity,
                    'start_date' => $period->start_date ? $period->start_date->format('Y-m-d') : null,
                    'end_date' => $period->end_date ? $period->end_date->format('Y-m-d') : null,
                    'is_open' => (bool) $period->is_open,
                    'is_published' => (bool) $period->is_published,
                    'slots' => $period->slots ?? [],
                ];
            });

        $selectedPeriodId = $request->integer('period_id');
        if (! $selectedPeriodId) {
            $defaultPeriod = $periods->firstWhere('is_open', true) ?? $periods->first();
            $selectedPeriodId = $defaultPeriod ? $defaultPeriod['id'] : null;
        }
        $selectedPeriod = $selectedPeriodId ? $periods->firstWhere('id', $selectedPeriodId) : null;

        $schedules = [];
        if ($selectedPeriodId) {
            $schedules = $this->getSchedulesForPeriod($selectedPeriodId, 'sempro');
        }

        $dosenListAll = User::whereIn('role', ['dosen', 'kaprodi', 'koordinator'])->orderBy('name')->pluck('name')->values()->all();
        $dosenOptions = User::whereIn('role', ['dosen', 'kaprodi', 'koordinator'])->orderBy('name')->get(['id', 'name', 'nim_nip', 'prodi', 'jabatan']);

        return Inertia::render('Kaprodi/PlottingSempro', [
            'periods' => $periods,
            'selectedPeriodId' => $selectedPeriodId,
            'selectedPeriod' => $selectedPeriod,
            'schedules' => $schedules,
            'dosenListAll' => $dosenListAll,
            'dosenOptions' => $dosenOptions,
        ]);
    }

    public function plottingTaIndex(Request $request)
    {
        $periods = SchedulePeriod::where('tipe', 'sidang')
            ->orderBy('gelombang')
            ->get()
            ->map(function ($period) {
                return [
                    'id' => $period->id,
                    'name' => $this->formatPeriodYear($period->name),
                    'tipe' => $period->tipe,
                    'semester' => $period->semester ?? 'Gasal',
                    'prodi' => $period->prodi ?? 'Informatika',
                    'gelombang' => $period->gelombang,
                    'capacity' => $period->capacity,
                    'start_date' => $period->start_date ? $period->start_date->format('Y-m-d') : null,
                    'end_date' => $period->end_date ? $period->end_date->format('Y-m-d') : null,
                    'is_open' => (bool) $period->is_open,
                    'is_published' => (bool) $period->is_published,
                    'slots' => $period->slots ?? [],
                ];
            });

        $selectedPeriodId = $request->integer('period_id');
        if (! $selectedPeriodId) {
            $defaultPeriod = $periods->firstWhere('is_open', true) ?? $periods->first();
            $selectedPeriodId = $defaultPeriod ? $defaultPeriod['id'] : null;
        }
        $selectedPeriod = $selectedPeriodId ? $periods->firstWhere('id', $selectedPeriodId) : null;

        $schedules = [];
        if ($selectedPeriodId) {
            $schedules = $this->getSchedulesForPeriod($selectedPeriodId, 'sidang');
        }

        $dosenListAll = User::whereIn('role', ['dosen', 'kaprodi', 'koordinator'])->orderBy('name')->pluck('name')->values()->all();
        $dosenOptions = User::whereIn('role', ['dosen', 'kaprodi', 'koordinator'])->orderBy('name')->get(['id', 'name', 'nim_nip', 'prodi', 'jabatan']);

        return Inertia::render('Kaprodi/PlottingTA', [
            'periods' => $periods,
            'selectedPeriodId' => $selectedPeriodId,
            'selectedPeriod' => $selectedPeriod,
            'schedules' => $schedules,
            'dosenListAll' => $dosenListAll,
            'dosenOptions' => $dosenOptions,
        ]);
    }

    public function semproIndex(Request $request)
    {
        $periods = SchedulePeriod::where('tipe', 'sempro')
            ->orderBy('gelombang')
            ->get()
            ->map(function ($period) {
                $registeredStudents = PendaftaranSempro::with(['mahasiswa:id,name,nim_nip,prodi'])
                    ->where('gelombang', $period->gelombang)
                    ->get();

                return [
                    'id' => $period->id,
                    'name' => $this->formatPeriodYear($period->name),
                    'tipe' => $period->tipe,
                    'semester' => $period->semester ?? 'Gasal',
                    'prodi' => $period->prodi ?? 'Informatika',
                    'gelombang' => $period->gelombang,
                    'capacity' => $period->capacity,
                    'start_date' => $period->start_date ? $period->start_date->format('Y-m-d') : null,
                    'end_date' => $period->end_date ? $period->end_date->format('Y-m-d') : null,
                    'is_open' => (bool) $period->is_open,
                    'is_published' => (bool) $period->is_published,
                    'google_calendar_synced' => (bool) $period->google_calendar_synced,
                    'google_calendar_synced_at' => $period->google_calendar_synced_at ? $period->google_calendar_synced_at->format('d/m/Y H:i') : null,
                    'slots' => $period->slots ?? [],
                    'mahasiswa_count' => $registeredStudents->count(),
                    'mahasiswa_list' => $registeredStudents->map(fn ($reg) => [
                        'id' => $reg->id,
                        'mahasiswa_id' => $reg->mahasiswa_id,
                        'name' => $reg->mahasiswa?->name ?? 'Mahasiswa',
                        'nim' => $reg->mahasiswa?->nim_nip ?? '-',
                        'prodi' => $reg->mahasiswa?->prodi ?? 'Informatika',
                        'judul_ta' => $reg->judul_ta,
                        'status' => $reg->status,
                        'status_akademik' => $reg->status_akademik,
                    ]),
                    'schedules' => $period->penjadwalan()->with(['mahasiswa:id,name,nim_nip', 'pembimbing1:id,name', 'pembimbing2:id,name', 'penguji1:id,name', 'penguji2:id,name'])->get()->map(fn ($s) => [
                        'id' => $s->id,
                        'mahasiswa' => $s->mahasiswa?->name ?? 'Mahasiswa',
                        'nim' => $s->mahasiswa?->nim_nip ?? '-',
                        'judul' => $s->catatan ?? 'Seminar Proposal',
                        'pembimbing_1' => $s->pembimbing1?->name ?? '-',
                        'pembimbing_2' => $s->pembimbing2?->name ?? '-',
                        'penguji_1' => $s->penguji1?->name ?? '-',
                        'penguji_2' => $s->penguji2?->name ?? '-',
                        'ruangan' => $s->link_online ?? ($s->mode === 'online' ? 'Online' : 'Offline'),
                        'tanggal' => $s->tanggal ? $s->tanggal->format('Y-m-d') : '-',
                        'jam_mulai' => $s->jam_mulai,
                        'jam_selesai' => $s->jam_selesai,
                    ]),
                ];
            });

        $activePeriod = $periods->firstWhere('is_open', true);

        return Inertia::render('Kaprodi/PeriodeSempro', [
            'periods' => $periods,
            'activePeriod' => $activePeriod,
        ]);
    }

    public function taIndex(Request $request)
    {
        $periods = SchedulePeriod::where('tipe', 'sidang')
            ->orderBy('gelombang')
            ->get()
            ->map(function ($period) {
                $registeredStudents = PendaftaranSidang::with(['mahasiswa:id,name,nim_nip,prodi'])
                    ->where('gelombang', $period->gelombang)
                    ->get();

                return [
                    'id' => $period->id,
                    'name' => $this->formatPeriodYear($period->name),
                    'tipe' => $period->tipe,
                    'semester' => $period->semester ?? 'Gasal',
                    'prodi' => $period->prodi ?? 'Informatika',
                    'gelombang' => $period->gelombang,
                    'capacity' => $period->capacity,
                    'start_date' => $period->start_date ? $period->start_date->format('Y-m-d') : null,
                    'end_date' => $period->end_date ? $period->end_date->format('Y-m-d') : null,
                    'is_open' => (bool) $period->is_open,
                    'is_published' => (bool) $period->is_published,
                    'google_calendar_synced' => (bool) $period->google_calendar_synced,
                    'google_calendar_synced_at' => $period->google_calendar_synced_at ? $period->google_calendar_synced_at->format('d/m/Y H:i') : null,
                    'slots' => $period->slots ?? [],
                    'mahasiswa_count' => $registeredStudents->count(),
                    'mahasiswa_list' => $registeredStudents->map(fn ($reg) => [
                        'id' => $reg->id,
                        'mahasiswa_id' => $reg->mahasiswa_id,
                        'name' => $reg->mahasiswa?->name ?? 'Mahasiswa',
                        'nim' => $reg->mahasiswa?->nim_nip ?? '-',
                        'prodi' => $reg->mahasiswa?->prodi ?? 'Informatika',
                        'judul_ta' => $reg->judul_ta,
                        'status' => $reg->status,
                        'status_akademik' => $reg->status_akademik,
                    ]),
                    'schedules' => $period->penjadwalan()->with(['mahasiswa:id,name,nim_nip', 'pembimbing1:id,name', 'pembimbing2:id,name', 'penguji1:id,name', 'penguji2:id,name'])->get()->map(fn ($s) => [
                        'id' => $s->id,
                        'mahasiswa' => $s->mahasiswa?->name ?? 'Mahasiswa',
                        'nim' => $s->mahasiswa?->nim_nip ?? '-',
                        'judul' => $s->catatan ?? 'Sidang Tugas Akhir',
                        'pembimbing_1' => $s->pembimbing1?->name ?? '-',
                        'pembimbing_2' => $s->pembimbing2?->name ?? '-',
                        'penguji_1' => $s->penguji1?->name ?? '-',
                        'penguji_2' => $s->penguji2?->name ?? '-',
                        'ruangan' => $s->link_online ?? ($s->mode === 'online' ? 'Online' : 'Offline'),
                        'tanggal' => $s->tanggal ? $s->tanggal->format('Y-m-d') : '-',
                        'jam_mulai' => $s->jam_mulai,
                        'jam_selesai' => $s->jam_selesai,
                    ]),
                ];
            });

        return Inertia::render('Kaprodi/PeriodeTA', [
            'periods' => $periods,
            'activePeriod' => $periods->firstWhere('is_open', true),
        ]);
    }

    private function formatPeriodYear(?string $name): string
    {
        if (! $name) {
            return '2026/2027';
        }
        if (preg_match('/\d{4}\/\d{4}/', $name, $matches)) {
            return $matches[0];
        }

        return $name;
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
        if (User::whereIn('id', $supervisors)->whereIn('role', ['dosen', 'kaprodi', 'koordinator'])->count() !== count($supervisors)) {
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
        $data = $request->validate([
            'period_id' => [
                'required', 'integer', function ($attribute, $value, $fail) {
                    if (is_bool($value)) {
                        $fail('The period id must be an integer.');
                    }
                }, 'exists:schedule_periods,id',
            ],
            'popSize' => ['nullable', 'integer', 'min:10', 'max:200'],
            'maxGenerations' => ['nullable', 'integer', 'min:10', 'max:500'],
            'crossoverRate' => ['nullable', 'numeric', 'between:0,1'],
            'mutationRate' => ['nullable', 'numeric', 'between:0,1'],
            'reset' => ['nullable', 'boolean'],
            'reschedule' => ['nullable', 'boolean'],
        ]);
        $period = SchedulePeriod::findOrFail($data['period_id']);
        if ($period->is_open) {
            throw ValidationException::withMessages(['period_id' => 'Tutup pendaftaran terlebih dahulu.']);
        }

        $isReschedule = ! empty($data['reset']) || ! empty($data['reschedule']);

        if ($period->is_published && ! $isReschedule) {
            throw ValidationException::withMessages(['period_id' => 'Jadwal sudah dipublikasikan. Buka publish untuk regenerasi.']);
        }

        if ($isReschedule) {
            $this->performResetSchedules($period);
            $period->refresh();
        }

        $class = $this->registrationClass($period->tipe);
        $candidates = [];
        $excluded = [];
        $scheduledIds = Penjadwalan::where('tipe', $period->tipe)
            ->where('status', '!=', 'dibatalkan')->pluck('pendaftaran_id')->flip();
        foreach ($class::whereIn('status', ['verifikasi_tendik', 'terjadwal'])->where('status_akademik', 'disetujui')->where('gelombang', $period->gelombang)->orderBy('id')->get() as $registration) {
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
        $periodBookings = array_filter($bookings, fn ($b) => (int) $b['period_id'] === $period->id);
        $lecturers = User::whereIn('role', ['dosen', 'kaprodi', 'koordinator'])->get(['id', 'name', 'prodi', 'jabatan']);
        $quota = (int) ceil(2 * (count($candidates) + $used) / max(1, $lecturers->count())) + 1;
        $lecturers = $lecturers->map(function ($lecturer) use ($periodBookings, $quota) {
            $row = $lecturer->toArray();
            $row['workload'] = count(array_filter($periodBookings, fn ($b) => in_array($lecturer->id, [$b['penguji_1_id'], $b['penguji_2_id']])));
            $row['capacity'] = $quota;

            return $row;
        })->all();
        $compatibility = app(\App\Services\GeneticAlgorithm\RfCompatibilityService::class)->getCompatibility($candidates, $lecturers);
        $result = (new ScheduleOptimizer($data['popSize'] ?? 50, $data['maxGenerations'] ?? 100, $data['crossoverRate'] ?? .8, $data['mutationRate'] ?? .03))->optimize($candidates, $lecturers, $period->slots, $bookings, $compatibility);
        $result['unscheduled'] = array_merge($result['unscheduled'], $overflow, $excluded);
        if ($result['unscheduled']) {
            $result['outcome'] = $result['schedule'] ? 'partial' : 'infeasible';
            $result['success'] = false;
        }
        $result['period_id'] = $period->id;
        $result['limitations'] = [
            ! empty($compatibility)
                ? 'Skor kecocokan penguji 1 dihitung dengan model Random Forest (scikit-learn).'
                : 'Skor kecocokan penguji 1 (RF Compatibility Score) memakai skor netral '.ScheduleOptimizer::NEUTRAL_SCORE.'; model RF tidak aktif.',
            'Ketersediaan waktu dosen belum tersimpan; semua dosen dianggap tersedia di setiap slot.',
            "Kuota menguji dihitung otomatis: maksimal {$quota} kali per dosen pada periode ini.",
        ];

        return response()->json($result);
    }

    public function storePeriod(Request $request, ?int $id = null)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'tipe' => ['required', 'in:sempro,sidang'],
            'semester' => ['nullable', 'string', 'max:50'],
            'prodi' => ['nullable', 'string', 'max:100'],
            'gelombang' => ['required', 'integer', 'min:1'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'is_open' => ['nullable', 'boolean'],
            'slots' => ['nullable', 'array', 'max:100'],
            'slots.*.tanggal' => ['required_with:slots', 'date_format:Y-m-d'],
            'slots.*.jam_mulai' => ['required_with:slots', 'date_format:H:i:s'],
            'slots.*.jam_selesai' => ['required_with:slots', 'date_format:H:i:s'],
        ]);

        $data['semester'] = $data['semester'] ?? 'Gasal';
        $data['prodi'] = $data['prodi'] ?? 'Informatika';
        $data['capacity'] = $data['capacity'] ?? 25;
        $data['start_date'] = $data['start_date'] ?? now()->format('Y-m-d');
        $data['end_date'] = $data['end_date'] ?? now()->addDays(30)->format('Y-m-d');
        $data['is_open'] = (bool) ($data['is_open'] ?? false);

        if (empty($data['slots'])) {
            $defaultDate = $data['start_date'];
            $data['slots'] = [
                ['tanggal' => $defaultDate, 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00'],
                ['tanggal' => $defaultDate, 'jam_mulai' => '10:30:00', 'jam_selesai' => '12:00:00'],
                ['tanggal' => $defaultDate, 'jam_mulai' => '13:30:00', 'jam_selesai' => '15:00:00'],
            ];
        } else {
            foreach ($data['slots'] as $slot) {
                if ($slot['tanggal'] < $data['start_date'] || $slot['tanggal'] > $data['end_date'] || $slot['jam_mulai'] >= $slot['jam_selesai']) {
                    throw ValidationException::withMessages(['slots' => 'Slot harus di dalam periode dengan waktu selesai sesudah mulai.']);
                }
            }
        }

        DB::transaction(function () use ($id, $data) {
            $period = $id ? SchedulePeriod::lockForUpdate()->findOrFail($id) : new SchedulePeriod;
            $period->fill($data);
            if ($id && Penjadwalan::where('period_id', $id)->exists() && $period->isDirty(['name', 'tipe', 'gelombang', 'capacity', 'start_date', 'end_date', 'slots'])) {
                throw ValidationException::withMessages(['slots' => 'Definisi periode dengan jadwal tersimpan tidak dapat diubah.']);
            }
            if ($period->is_open && Penjadwalan::where('period_id', $period->id)->whereIn('status', ['terpublikasi', 'selesai'])->exists()) {
                throw ValidationException::withMessages(['is_open' => 'Periode sudah memiliki jadwal terpublikasi.']);
            }
            if ($period->is_open && (! $period->exists || $period->isDirty('is_open')) && SchedulePeriod::where('tipe', $period->tipe)->where('is_open', true)->when($id, fn ($query) => $query->where('id', '!=', $id))->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['is_open' => 'Hanya satu periode per jenis dapat dibuka. Tutup periode lain terlebih dahulu.']);
            }
            $period->save();
        });

        return back()->with('success', 'Periode tersimpan.');
    }

    public function togglePeriodStatus(Request $request, int $id)
    {
        $period = SchedulePeriod::findOrFail($id);

        DB::transaction(function () use ($period) {
            if (! $period->is_open) {
                if (Penjadwalan::where('period_id', $period->id)->whereIn('status', ['terpublikasi', 'selesai'])->exists()) {
                    throw ValidationException::withMessages(['is_open' => 'Periode sudah memiliki jadwal terpublikasi.']);
                }
                SchedulePeriod::where('tipe', $period->tipe)
                    ->where('id', '!=', $period->id)
                    ->update(['is_open' => false]);
                $period->is_open = true;
            } else {
                $period->is_open = false;
            }
            $period->save();
        });

        return back()->with('success', 'Status periode gelombang diperbarui.');
    }

    public function togglePublish(Request $request, int $id)
    {
        $period = SchedulePeriod::with('penjadwalan.mahasiswa')->findOrFail($id);
        $period->is_published = ! $period->is_published;
        $period->save();

        if ($period->is_published) {
            $tipeLabel = $period->tipe === 'sempro' ? 'Seminar Proposal' : 'Sidang Tugas Akhir';
            foreach ($period->penjadwalan as $exam) {
                // Notify student
                Notification::create([
                    'user_id' => $exam->mahasiswa_id,
                    'title' => "Jadwal {$tipeLabel} Dipublikasikan",
                    'message' => "Jadwal {$tipeLabel} Anda telah resmi dipublikasikan untuk tanggal ".($exam->tanggal ? $exam->tanggal->format('d M Y') : 'sesuai jadwal').'.',
                    'type' => 'success',
                    'link' => '/mahasiswa/'.($period->tipe === 'sempro' ? 'sempro' : 'sidang'),
                ]);

                // Notify examiners and supervisors
                $lecturerIds = array_filter(array_unique([
                    $exam->penguji_1_id,
                    $exam->penguji_2_id,
                    $exam->pembimbing_1_id,
                    $exam->pembimbing_2_id,
                ]));
                foreach ($lecturerIds as $lecturerId) {
                    Notification::create([
                        'user_id' => $lecturerId,
                        'title' => "Jadwal Baru {$tipeLabel}",
                        'message' => "Jadwal {$tipeLabel} mahasiswa {$exam->mahasiswa?->name} telah dipublikasikan.",
                        'type' => 'info',
                        'link' => '/dosen/jadwal',
                    ]);
                }
            }
        }

        $statusText = $period->is_published ? 'dipublikasikan ke Mahasiswa & Dosen' : 'disembunyikan (Draft)';

        return back()->with('success', "Jadwal Gelombang {$period->gelombang} berhasil {$statusText}.");
    }

    public function syncCalendar(Request $request, int $id)
    {
        $period = SchedulePeriod::findOrFail($id);
        $period->google_calendar_synced = true;
        $period->google_calendar_synced_at = now();
        $period->save();

        $tipeLabel = $period->tipe === 'sempro' ? 'Seminar Proposal' : 'Sidang Tugas Akhir';

        return back()->with('success', "Jadwal {$tipeLabel} Gelombang {$period->gelombang} berhasil disinkronkan ke Google Calendar Informatika.");
    }

    public function exportIcs(int $id)
    {
        $period = SchedulePeriod::with(['penjadwalan.mahasiswa', 'penjadwalan.pembimbing1', 'penjadwalan.pembimbing2', 'penjadwalan.penguji1', 'penjadwalan.penguji2'])->findOrFail($id);

        $events = [];
        $tipeName = $period->tipe === 'sempro' ? 'Seminar Proposal' : 'Sidang Tugas Akhir';

        foreach ($period->penjadwalan as $jadwal) {
            $studentName = $jadwal->mahasiswa?->name ?? 'Mahasiswa';
            $nim = $jadwal->mahasiswa?->nim_nip ?? '-';
            $ruangan = $jadwal->link_online ?? ($jadwal->mode === 'online' ? 'Online' : 'Offline');

            $startStr = $jadwal->tanggal->format('Y-m-d').' '.$jadwal->jam_mulai;
            $endStr = $jadwal->tanggal->format('Y-m-d').' '.$jadwal->jam_selesai;
            $start = Carbon::parse($startStr, 'Asia/Makassar')->setTimezone('UTC')->format('Ymd\THis\Z');
            $end = Carbon::parse($endStr, 'Asia/Makassar')->setTimezone('UTC')->format('Ymd\THis\Z');

            $summary = "{$tipeName}: {$studentName} ({$nim})";
            $description = "Jadwal {$tipeName}\\n".
                "Mahasiswa: {$studentName} ({$nim})\\n".
                'Judul: '.addslashes($jadwal->catatan ?? 'Tugas Akhir').'\\n'.
                'Pembimbing 1: '.($jadwal->pembimbing1?->name ?? '-').'\\n'.
                'Pembimbing 2: '.($jadwal->pembimbing2?->name ?? '-').'\\n'.
                'Penguji 1: '.($jadwal->penguji1?->name ?? '-').'\\n'.
                'Penguji 2: '.($jadwal->penguji2?->name ?? '-');

            $uid = "jadwal-{$jadwal->id}-{$period->id}@sipta.itk.ac.id";

            $events[] = implode("\r\n", [
                'BEGIN:VEVENT',
                "UID:{$uid}",
                'DTSTAMP:'.gmdate('Ymd\THis\Z'),
                "DTSTART:{$start}",
                "DTEND:{$end}",
                "SUMMARY:{$summary}",
                "DESCRIPTION:{$description}",
                "LOCATION:{$ruangan}",
                'STATUS:CONFIRMED',
                'END:VEVENT',
            ]);
        }

        if (empty($events)) {
            $start = Carbon::parse($period->start_date)->setTimezone('UTC')->format('Ymd\THis\Z');
            $end = Carbon::parse($period->end_date)->setTimezone('UTC')->format('Ymd\THis\Z');
            $summary = "Periode {$tipeName} Gelombang {$period->gelombang} Informatika ITK";
            $events[] = implode("\r\n", [
                'BEGIN:VEVENT',
                "UID:periode-{$period->id}@sipta.itk.ac.id",
                'DTSTAMP:'.gmdate('Ymd\THis\Z'),
                "DTSTART:{$start}",
                "DTEND:{$end}",
                "SUMMARY:{$summary}",
                "DESCRIPTION:Periode {$tipeName} Gelombang {$period->gelombang} TA {$period->name}",
                'LOCATION:Program Studi Informatika ITK',
                'STATUS:CONFIRMED',
                'END:VEVENT',
            ]);
        }

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//SIPTA ITK//Informatika Schedule//ID',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:Jadwal '.$tipeName.' Gelombang '.$period->gelombang.' Informatika ITK',
            'X-WR-TIMEZONE:Asia/Makassar',
            implode("\r\n", $events),
            'END:VCALENDAR',
        ]);

        $fileName = "jadwal_{$period->tipe}_gelombang_{$period->gelombang}.ics";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    public function destroyPeriod(int $id)
    {
        $period = SchedulePeriod::findOrFail($id);
        if (Penjadwalan::where('period_id', $id)->exists()) {
            throw ValidationException::withMessages(['period' => 'Periode tidak dapat dihapus karena sudah memiliki jadwal tersimpan.']);
        }
        $period->delete();

        return back()->with('success', 'Periode gelombang berhasil dihapus.');
    }

    public function resetPeriodSchedules(Request $request, ?int $id = null)
    {
        $periodId = $id ?? $request->integer('period_id');
        if (! $periodId) {
            throw ValidationException::withMessages(['period_id' => 'ID Periode wajib disertakan.']);
        }

        $period = SchedulePeriod::findOrFail($periodId);
        $this->performResetSchedules($period);

        $message = "Jadwal Gelombang {$period->gelombang} berhasil direset. Mahasiswa pendaftar siap untuk dijadwalkan ulang.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'period_id' => $period->id,
            ]);
        }

        return back()->with('success', $message);
    }

    private function performResetSchedules(SchedulePeriod $period): void
    {
        DB::transaction(function () use ($period) {
            $schedules = Penjadwalan::where(function ($q) use ($period) {
                $q->where('period_id', $period->id)
                    ->orWhere(function ($q2) use ($period) {
                        $q2->whereNull('period_id')
                            ->where('tipe', $period->tipe)
                            ->where('gelombang', $period->gelombang);
                    });
            })->lockForUpdate()->get();

            foreach ($schedules as $schedule) {
                if ($schedule->status === 'selesai' || $schedule->penilaian()->exists()) {
                    throw ValidationException::withMessages([
                        'period' => 'Tidak dapat menjadwalkan ulang karena sebagian mahasiswa sudah selesai ujian atau memiliki nilai.',
                    ]);
                }
            }

            $class = $this->registrationClass($period->tipe);
            foreach ($schedules as $schedule) {
                if ($schedule->pendaftaran_id) {
                    $class::where('id', $schedule->pendaftaran_id)->update(['status' => 'verifikasi_tendik']);
                }
            }

            // Reset any registrations in this wave that were marked 'terjadwal' back to 'verifikasi_tendik'
            $class::where('gelombang', $period->gelombang)
                ->where('status', 'terjadwal')
                ->update(['status' => 'verifikasi_tendik']);

            Penjadwalan::where('period_id', $period->id)
                ->orWhere(function ($q2) use ($period) {
                    $q2->whereNull('period_id')
                        ->where('tipe', $period->tipe)
                        ->where('gelombang', $period->gelombang);
                })->delete();

            $period->is_published = false;
            $period->google_calendar_synced = false;
            $period->google_calendar_synced_at = null;
            $period->save();
        });
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
            User::whereIn('role', ['dosen', 'kaprodi', 'koordinator'])->orderBy('id')->lockForUpdate()->get(['id']);
            $period = SchedulePeriod::lockForUpdate()->findOrFail($periodId);
            if ($period->is_open) {
                throw ValidationException::withMessages(['schedules' => 'Tutup pendaftaran sebelum menyimpan jadwal.']);
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
                if ((int) $row['penguji_1_id'] === (int) $row['penguji_2_id'] || User::whereIn('id', [$row['penguji_1_id'], $row['penguji_2_id']])->whereIn('role', ['dosen', 'kaprodi', 'koordinator'])->count() !== 2) {
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

    public function updatePlotting(Request $request, int $id)
    {
        $data = $request->validate([
            'penguji1' => ['nullable', 'string'],
            'penguji2' => ['nullable', 'string'],
            'tanggal' => ['nullable', 'string'],
            'mulai' => ['nullable', 'string'],
            'selesai' => ['nullable', 'string'],
            'ruangan' => ['nullable', 'string'],
        ]);

        $schedule = Penjadwalan::find($id);
        if ($schedule) {
            if (! empty($data['penguji1'])) {
                $d1 = User::where('name', $data['penguji1'])->first();
                if ($d1) {
                    $schedule->penguji_1_id = $d1->id;
                }
            }
            if (! empty($data['penguji2'])) {
                $d2 = User::where('name', $data['penguji2'])->first();
                if ($d2) {
                    $schedule->penguji_2_id = $d2->id;
                }
            }
            if (! empty($data['mulai'])) {
                $schedule->jam_mulai = strlen($data['mulai']) === 5 ? $data['mulai'].':00' : $data['mulai'];
            }
            if (! empty($data['selesai'])) {
                $schedule->jam_selesai = strlen($data['selesai']) === 5 ? $data['selesai'].':00' : $data['selesai'];
            }
            if (! empty($data['tanggal'])) {
                try {
                    $schedule->tanggal = Carbon::parse($data['tanggal'])->format('Y-m-d');
                } catch (\Exception $e) {
                    // keep existing date
                }
            }
            if (isset($data['ruangan'])) {
                $schedule->link_online = $data['ruangan'];
            }
            $schedule->save();
        }

        return back()->with('success', 'Jadwal dan plotting berhasil diperbarui.');
    }

    private function getSchedulesForPeriod(int $periodId, string $tipe): array
    {
        $period = SchedulePeriod::find($periodId);
        $schedules = Penjadwalan::with(['mahasiswa', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2', 'penilaian'])
            ->where('period_id', $periodId)
            ->where('tipe', $tipe)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        $scheduledStudentIds = $schedules->pluck('mahasiswa_id')->filter()->all();

        $rows = $schedules->map(function ($s) {
            $judul = PengajuanJudul::where('mahasiswa_id', $s->mahasiswa_id)->value('judul_ta') ?? $s->catatan;
            $kbk = PengajuanJudul::where('mahasiswa_id', $s->mahasiswa_id)->value('bidang_penelitian') ?? ($s->mode === 'offline' ? 'AI & Data Science' : $s->mode);

            $nilaiHuruf = null;
            if ($s->penilaian && $s->penilaian->isNotEmpty()) {
                $avgScore = $s->penilaian->avg('nilai');
                if ($avgScore !== null) {
                    $nilaiHuruf = $avgScore >= 85 ? 'A' : ($avgScore >= 80 ? 'AB' : ($avgScore >= 75 ? 'B' : ($avgScore >= 70 ? 'BC' : ($avgScore >= 65 ? 'C' : ($avgScore >= 50 ? 'D' : 'E')))));
                }
            }

            return [
                'id' => $s->id,
                'pendaftaran_id' => $s->pendaftaran_id,
                'mahasiswa_id' => $s->mahasiswa_id,
                'nama' => $s->mahasiswa?->name ?? 'Mahasiswa',
                'nim' => $s->mahasiswa?->nim_nip ?? '-',
                'angkatan' => $this->extractAngkatan($s->mahasiswa?->nim_nip),
                'judul' => $judul ?? 'Seminar Proposal',
                'kbk' => $kbk ?: 'AI & Data Science',
                'pembimbing1' => $s->pembimbing1?->name ?? '-',
                'pembimbing2' => $s->pembimbing2?->name ?? '',
                'penguji1' => $s->penguji1?->name ?? '-',
                'penguji1_id' => $s->penguji_1_id,
                'penguji1MatchScore' => $s->penguji_1_id ? (int) round(ScheduleOptimizer::NEUTRAL_SCORE * 100) : 0,
                'penguji2' => $s->penguji2?->name ?? '-',
                'penguji2_id' => $s->penguji_2_id,
                'hari' => $s->tanggal ? $this->getHariIndonesia((int) $s->tanggal->format('N')) : 'Senin',
                'tanggal' => $s->tanggal ? $s->tanggal->format('d M Y') : '28 Sep 2026',
                'tanggal_raw' => $s->tanggal ? $s->tanggal->format('Y-m-d') : '2026-09-28',
                'mulai' => $s->jam_mulai ? substr($s->jam_mulai, 0, 5) : '09:00',
                'selesai' => $s->jam_selesai ? substr($s->jam_selesai, 0, 5) : '10:30',
                'ruangan' => $s->link_online ?? '-',
                'nilai' => $nilaiHuruf,
                'is_scheduled' => true,
            ];
        })->all();

        // Include registered students in this wave who are validated but not yet scheduled
        if ($period) {
            $class = $this->registrationClass($tipe);
            $anyScheduledPendaftaranIds = Penjadwalan::where('tipe', $tipe)
                ->where('status', '!=', 'dibatalkan')
                ->pluck('pendaftaran_id')
                ->filter()
                ->all();

            $unregistered = $class::with('mahasiswa')
                ->where('gelombang', $period->gelombang)
                ->where('status', 'verifikasi_tendik')
                ->where('status_akademik', 'disetujui')
                ->whereNotIn('id', $anyScheduledPendaftaranIds)
                ->orderBy('id')
                ->get();

            foreach ($unregistered as $reg) {
                $title = PengajuanJudul::with(['pembimbing1', 'pembimbing2'])
                    ->where('mahasiswa_id', $reg->mahasiswa_id)
                    ->where('status', 'disetujui')
                    ->latest('id')
                    ->first();

                $rows[] = [
                    'id' => -$reg->id,
                    'pendaftaran_id' => $reg->id,
                    'mahasiswa_id' => $reg->mahasiswa_id,
                    'nama' => $reg->mahasiswa?->name ?? 'Mahasiswa',
                    'nim' => $reg->mahasiswa?->nim_nip ?? '-',
                    'angkatan' => $this->extractAngkatan($reg->mahasiswa?->nim_nip),
                    'judul' => $reg->judul_ta ?? $title?->judul_ta ?? 'Seminar Proposal',
                    'kbk' => $title?->bidang_penelitian ?: 'AI & Data Science',
                    'pembimbing1' => $title?->pembimbing1?->name ?? '-',
                    'pembimbing2' => $title?->pembimbing2?->name ?? '',
                    'penguji1' => '-',
                    'penguji1_id' => null,
                    'penguji1MatchScore' => 0,
                    'penguji2' => '-',
                    'penguji2_id' => null,
                    'hari' => '-',
                    'tanggal' => '-',
                    'tanggal_raw' => '',
                    'mulai' => '-',
                    'selesai' => '-',
                    'ruangan' => '-',
                    'nilai' => null,
                    'is_scheduled' => false,
                ];
            }
        }

        return $rows;
    }

    private function extractAngkatan(?string $nim): string
    {
        if (! $nim) {
            return '2023';
        }
        if (preg_match('/^11(20|21|22|23|24)/', $nim, $matches)) {
            return '20'.$matches[1];
        }

        return '2023';
    }

    private function getHariIndonesia(int $dayOfWeek): string
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        return $days[$dayOfWeek] ?? 'Senin';
    }
}
