<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSempro;
use App\Models\PengajuanJudul;
use App\Models\SchedulePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PendaftaranSemproController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $sempro = PendaftaranSempro::where('mahasiswa_id', $user->id)
            ->latest()
            ->first();

        $pengajuanJudul = PengajuanJudul::with(['pembimbing1:id,name', 'pembimbing2:id,name'])
            ->where('mahasiswa_id', $user->id)
            ->where('status', 'disetujui')
            ->first();

        $pembimbingList = [];
        if ($pengajuanJudul?->pembimbing1) {
            $pembimbingList[] = [
                'nama' => $pengajuanJudul->pembimbing1->name,
                'role' => 'Pembimbing 1',
                'variant' => 'rose',
            ];
        }
        if ($pengajuanJudul?->pembimbing2) {
            $pembimbingList[] = [
                'nama' => $pengajuanJudul->pembimbing2->name,
                'role' => 'Pembimbing 2',
                'variant' => 'rose',
            ];
        }

        $jadwal = \App\Models\Penjadwalan::with(['penguji1:id,name', 'penguji2:id,name', 'ruangan:id,nama_ruangan'])
            ->where('mahasiswa_id', $user->id)
            ->where('tipe', 'sempro')
            ->latest('id')
            ->first();

        $pengujiList = [];
        if ($jadwal?->penguji1) {
            $pengujiList[] = [
                'nama' => $jadwal->penguji1->name,
                'role' => 'Penguji 1',
                'variant' => 'rose',
            ];
        }
        if ($jadwal?->penguji2) {
            $pengujiList[] = [
                'nama' => $jadwal->penguji2->name,
                'role' => 'Penguji 2',
                'variant' => 'rose',
            ];
        }

        $pelaksanaan = null;
        if ($jadwal) {
            $penilaianList = \App\Models\Penilaian::where('penjadwalan_id', $jadwal->id)->get();
            $rataRata = $penilaianList->isNotEmpty() ? round($penilaianList->avg('nilai'), 2) : null;

            $pelaksanaan = [
                'tanggal' => $jadwal->tanggal ? \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d M Y') : null,
                'jam' => $jadwal->jam_mulai && $jadwal->jam_selesai ? substr($jadwal->jam_mulai, 0, 5) . ' - ' . substr($jadwal->jam_selesai, 0, 5) : null,
                'mode' => ucfirst($jadwal->mode ?? 'offline'),
                'status' => $jadwal->status,
                'nilai_akhir' => $rataRata,
                'catatan' => $jadwal->catatan,
            ];
        }

        return Inertia::render('Pendaftaran/Sempro', [
            'sempro' => $sempro,
            'defaultJudul' => $pengajuanJudul?->judul_ta ?? null,
            'pembimbingList' => $pembimbingList,
            'pengujiList' => $pengujiList,
            'pelaksanaan' => $pelaksanaan,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_ta' => ['required', 'string', 'max:255'],
            'bentuk_ta' => ['required', 'string', 'max:150'],
            'lokasi_mitra' => ['nullable', 'string', 'max:255'],
            'skor_iaet' => ['nullable'],
            'lembar_kehadiran_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'proposal_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'turnitin_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'iaet_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $user = Auth::user();
        $existing = PendaftaranSempro::where('mahasiswa_id', $user->id)->first();
        if ($existing && in_array($existing->status, ['terjadwal', 'selesai'])) {
            throw ValidationException::withMessages(['judul_ta' => 'Pendaftaran terjadwal atau selesai tidak dapat diubah.']);
        }

        $periods = SchedulePeriod::where('tipe', 'sempro')->where('is_open', true)->get();
        if ($periods->count() > 1 || ($periods->isEmpty() && ! $existing)) {
            throw ValidationException::withMessages(['gelombang' => 'Harus tersedia tepat satu periode penjadwalan terbuka untuk pendaftaran. Hubungi koordinator.']);
        }
        // Corrections keep their existing wave when registration is closed.
        $wave = $periods->first()?->gelombang ?? $existing->gelombang;

        // Upload berkas jika ada
        $uploaded = [];
        $fileFields = ['lembar_kehadiran_file', 'proposal_file', 'turnitin_file', 'iaet_file'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store("dokumen/sempro/{$user->id}", 'local');
                $uploaded[$field] = $path;
            }
        }

        PendaftaranSempro::updateOrCreate(
            ['mahasiswa_id' => $user->id],
            array_merge([
                'judul_ta' => $validated['judul_ta'],
                'bentuk_ta' => $validated['bentuk_ta'],
                'lokasi_mitra' => $validated['lokasi_mitra'] ?? null,
                'gelombang' => $wave,
                'status' => 'menunggu',
                'status_akademik' => 'menunggu',
            ], $uploaded)
        );

        return redirect()->back()->with('success', 'Formulir pendaftaran Seminar Proposal berhasil diajukan!');
    }
}
