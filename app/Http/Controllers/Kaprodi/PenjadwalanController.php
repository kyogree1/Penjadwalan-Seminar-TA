<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\Penjadwalan;
use App\Models\User;
use App\Services\GeneticAlgorithm\ScheduleOptimizer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PenjadwalanController extends Controller
{
    public function index(): Response
    {
        $penjadwalanList = Penjadwalan::with([
            'mahasiswa:id,name,username',
            'pembimbing1:id,name',
            'pembimbing2:id,name',
            'penguji1:id,name',
            'penguji2:id,name',
            'ruangan:id,nama_ruangan',
        ])
            ->latest()
            ->get();

        $dosenList = User::where('role', 'dosen')
            ->select('id', 'name', 'username')
            ->get();

        return Inertia::render('Koordinator/Penjadwalan', [
            'penjadwalanList' => $penjadwalanList,
            'dosenOptions' => $dosenList,
        ]);
    }

    public function generateGa(Request $request)
    {
        $validated = $request->validate([
            'popSize' => ['nullable', 'integer', 'min:10', 'max:200'],
            'maxGenerations' => ['nullable', 'integer', 'min:10', 'max:500'],
            'crossoverRate' => ['nullable', 'numeric'],
            'mutationRate' => ['nullable', 'numeric'],
        ]);

        $candidates = PendaftaranSempro::with('mahasiswa:id,name,username')
            ->where('status', 'verifikasi_tendik')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'nama' => $s->mahasiswa->name ?? 'Mahasiswa',
                    'nim' => $s->mahasiswa->username ?? '',
                    'judul' => $s->judul_ta,
                    'kbk' => 'Informatika',
                    'supervisors' => [],
                ];
            })
            ->toArray();

        $lecturers = User::where('role', 'dosen')->get(['id', 'name'])->toArray();

        $availableSlots = [
            ['tanggal' => '2026-09-28', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00'],
            ['tanggal' => '2026-09-28', 'jam_mulai' => '10:45:00', 'jam_selesai' => '12:15:00'],
            ['tanggal' => '2026-09-29', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00'],
            ['tanggal' => '2026-09-29', 'jam_mulai' => '13:30:00', 'jam_selesai' => '15:00:00'],
            ['tanggal' => '2026-09-30', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00'],
            ['tanggal' => '2026-09-30', 'jam_mulai' => '10:45:00', 'jam_selesai' => '12:15:00'],
        ];

        $optimizer = new ScheduleOptimizer(
            $validated['popSize'] ?? 50,
            $validated['maxGenerations'] ?? 100,
            $validated['crossoverRate'] ?? 0.80,
            $validated['mutationRate'] ?? 0.03
        );

        $result = $optimizer->optimize($candidates, $lecturers, $availableSlots);

        return response()->json($result);
    }

    public function simpan(Request $request)
    {
        $validated = $request->validate([
            'schedules' => ['required', 'array'],
            'schedules.*.pendaftaran_id' => ['required'],
            'schedules.*.tipe' => ['required', 'in:sempro,sidang'],
            'schedules.*.mahasiswa_id' => ['required', 'exists:users,id'],
            'schedules.*.penguji_1_id' => ['required', 'exists:users,id'],
            'schedules.*.penguji_2_id' => ['required', 'exists:users,id'],
            'schedules.*.tanggal' => ['required', 'date'],
            'schedules.*.jam_mulai' => ['required'],
            'schedules.*.jam_selesai' => ['required'],
        ]);

        foreach ($validated['schedules'] as $sched) {
            Penjadwalan::updateOrCreate(
                [
                    'tipe' => $sched['tipe'],
                    'pendaftaran_id' => $sched['pendaftaran_id'],
                ],
                array_merge($sched, ['status' => 'terpublikasi'])
            );
        }

        return redirect()->back()->with('success', 'Seluruh hasil rekomendasi optimasi AI berhasil diterapkan ke jadwal!');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'penguji_1_id' => ['nullable'],
            'penguji_2_id' => ['nullable'],
            'tanggal' => ['nullable', 'date'],
            'jam_mulai' => ['nullable'],
            'jam_selesai' => ['nullable'],
            'ruangan_id' => ['nullable', 'exists:ruangan,id'],
            'status' => ['nullable', 'in:draft,terpublikasi,selesai,dibatalkan'],
        ]);

        $penjadwalan = Penjadwalan::findOrFail($id);
        $penjadwalan->update($validated);

        return redirect()->back()->with('success', 'Penjadwalan dan plotting penguji berhasil diperbarui!');
    }
}
