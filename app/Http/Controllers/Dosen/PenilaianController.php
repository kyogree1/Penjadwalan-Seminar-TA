<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\Penilaian;
use App\Models\Penjadwalan;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PenilaianController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        // Cari seminar di mana dosen ini bertindak sebagai pembimbing atau penguji
        $jadwalUji = Penjadwalan::with([
            'mahasiswa:id,name,username,nim_nip',
            'penilaian',
        ])
            ->where(function ($q) use ($user) {
                $q->where('penguji_1_id', $user->id)
                    ->orWhere('penguji_2_id', $user->id)
                    ->orWhere('pembimbing_1_id', $user->id)
                    ->orWhere('pembimbing_2_id', $user->id);
            })
            ->latest('tanggal')
            ->get();

        return Inertia::render('Dosen/Penilaian', [
            'dbJadwalUji' => $jadwalUji->map(function ($exam) use ($user) {
                $ids = $exam->examinerIds();
                $scores = $exam->penilaian->whereIn('dosen_id', $ids);
                $mine = $scores->firstWhere('dosen_id', $user->id);
                $data = $exam->toArray();
                unset($data['penilaian']);

                return array_merge($data, [
                    'documents' => ExamDocumentController::links($exam, $user->id),
                    'penilaian_saya' => $mine?->only(['nilai', 'catatan']),
                    'jumlah_penilaian' => $scores->count(),
                    'nilai_akhir' => count($ids) === 2 && $scores->count() === 2 ? round($scores->avg('nilai'), 2) : null,
                    'dapat_menilai' => $exam->status === 'terpublikasi' && count($ids) === 2 && in_array($user->id, $ids) && ! $mine,
                ]);
            }),
        ]);
    }

    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'nilai' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            'catatan' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $id, $validated) {
            $exam = Penjadwalan::where(function ($query) use ($request) {
                $query->where('penguji_1_id', $request->user()->id)
                    ->orWhere('penguji_2_id', $request->user()->id);
            })->lockForUpdate()->findOrFail($id);
            $ids = $exam->examinerIds();
            if ($exam->penilaian()->where('dosen_id', $request->user()->id)->exists()) {
                throw ValidationException::withMessages(['nilai' => 'Nilai sudah disimpan dan tidak dapat ditimpa.']);
            }
            if ($exam->status !== 'terpublikasi' || count($ids) !== 2) {
                throw ValidationException::withMessages(['nilai' => 'Penilaian memerlukan jadwal terpublikasi dengan dua penguji berbeda.']);
            }
            try {
                Penilaian::create([
                    'penjadwalan_id' => $exam->id,
                    'dosen_id' => $request->user()->id,
                    'nilai' => $validated['nilai'],
                    'catatan' => $validated['catatan'] ?? null,
                ]);
            } catch (UniqueConstraintViolationException $e) {
                throw ValidationException::withMessages(['nilai' => 'Nilai sudah disimpan dan tidak dapat ditimpa.']);
            }
            if ($exam->penilaian()->whereIn('dosen_id', $ids)->count() === 2) {
                $exam->update(['status' => 'selesai']);
                $model = $exam->tipe === 'sempro' ? PendaftaranSempro::class : PendaftaranSidang::class;
                $model::whereKey($exam->pendaftaran_id)->where('mahasiswa_id', $exam->mahasiswa_id)
                    ->where('status', 'terjadwal')->update(['status' => 'selesai']);
            }
        });

        return redirect()->back()->with('success', 'Nilai ujian dan evaluasi berhasil disimpan!');
    }
}
