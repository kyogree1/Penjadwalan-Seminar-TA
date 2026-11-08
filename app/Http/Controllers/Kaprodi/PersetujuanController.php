<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\RescheduleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PersetujuanController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $judulQuery = PengajuanJudul::with([
            'mahasiswa:id,name,username,nim_nip,angkatan',
            'pembimbing1:id,name',
            'pembimbing2:id,name',
        ]);

        if ($user) {
            $judulQuery->where(function ($query) use ($user) {
                $query->where('pembimbing_1_id', $user->id)
                    ->orWhere('pembimbing_2_id', $user->id);
            });
        }

        $judulList = $judulQuery->latest()->get();

        $semproList = PendaftaranSempro::with([
            'mahasiswa:id,name,username,nim_nip,angkatan',
            'penjadwalan.penilaian',
            'penjadwalan.pembimbing1:id,name',
            'penjadwalan.pembimbing2:id,name',
            'penjadwalan.penguji1:id,name',
            'penjadwalan.penguji2:id,name',
        ])
            ->latest()
            ->get()
            ->map(function ($sempro) {
                $jadwal = $sempro->penjadwalan;
                $penilaian = $jadwal?->penilaian ?? collect();
                $semproArr = $sempro->toArray();
                $semproArr['penjadwalan_id'] = $jadwal?->id;
                $semproArr['penguji1'] = $jadwal?->penguji1?->name;
                $semproArr['penguji2'] = $jadwal?->penguji2?->name;
                $semproArr['pembimbing1'] = $jadwal?->pembimbing1?->name;
                $semproArr['pembimbing2'] = $jadwal?->pembimbing2?->name;
                $semproArr['tanggal'] = $jadwal?->tanggal ? $jadwal->tanggal->translatedFormat('l, d M Y') : null;
                $semproArr['waktu'] = $jadwal ? (substr($jadwal->jam_mulai, 0, 5).' - '.substr($jadwal->jam_selesai, 0, 5)) : null;
                $semproArr['nilai'] = $penilaian->count() > 0 ? (string) round($penilaian->avg('nilai'), 2) : null;

                return $semproArr;
            });

        $sidangList = PendaftaranSidang::with([
            'mahasiswa:id,name,username,nim_nip,angkatan',
            'penjadwalan.penilaian',
            'penjadwalan.pembimbing1:id,name',
            'penjadwalan.pembimbing2:id,name',
            'penjadwalan.penguji1:id,name',
            'penjadwalan.penguji2:id,name',
        ])
            ->latest()
            ->get()
            ->map(function ($sidang) {
                $jadwal = $sidang->penjadwalan;
                $penilaian = $jadwal?->penilaian ?? collect();
                $nilaiAvg = null;
                $nilaiHuruf = null;

                if ($penilaian->isNotEmpty()) {
                    $nilaiAvg = round($penilaian->avg('nilai'), 2);
                    $nilaiHuruf = match (true) {
                        $nilaiAvg >= 85 => 'A',
                        $nilaiAvg >= 80 => 'AB',
                        $nilaiAvg >= 75 => 'B',
                        $nilaiAvg >= 70 => 'BC',
                        $nilaiAvg >= 65 => 'C',
                        $nilaiAvg >= 50 => 'D',
                        default => 'E',
                    };
                }

                $sidangArr = $sidang->toArray();
                $sidangArr['penjadwalan_id'] = $jadwal?->id;
                $sidangArr['nilai'] = $nilaiHuruf ?? ($nilaiAvg ? (string) $nilaiAvg : null);
                $sidangArr['nilai_angka'] = $nilaiAvg;
                $sidangArr['jumlah_penilai'] = $penilaian->count();
                $sidangArr['penguji1'] = $jadwal?->penguji1?->name;
                $sidangArr['penguji2'] = $jadwal?->penguji2?->name;
                $sidangArr['pembimbing1'] = $jadwal?->pembimbing1?->name;
                $sidangArr['pembimbing2'] = $jadwal?->pembimbing2?->name;
                $sidangArr['tanggal'] = $jadwal?->tanggal ? $jadwal->tanggal->translatedFormat('l, d M Y') : null;
                $sidangArr['waktu'] = $jadwal ? (substr($jadwal->jam_mulai, 0, 5).' - '.substr($jadwal->jam_selesai, 0, 5)) : null;

                return $sidangArr;
            });

        $rescheduleList = RescheduleRequest::with([
            'penjadwalan.mahasiswa',
            'penjadwalan.pembimbing1',
            'penjadwalan.pembimbing2',
            'penjadwalan.penguji1',
            'penjadwalan.penguji2',
            'pemohon',
            'pemutus',
        ])
            ->latest()
            ->get()
            ->map(function ($req) {
                $exam = $req->penjadwalan;
                $mahasiswa = $exam?->mahasiswa;
                $pemohon = $req->pemohon;
                $pemutus = $req->pemutus;

                $dosenRole = 'Penguji 1';
                if ($exam && $pemohon) {
                    if ($pemohon->id === $exam->pembimbing_1_id) {
                        $dosenRole = 'Pembimbing 1';
                    } elseif ($pemohon->id === $exam->pembimbing_2_id) {
                        $dosenRole = 'Pembimbing 2';
                    } elseif ($pemohon->id === $exam->penguji_2_id) {
                        $dosenRole = 'Penguji 2';
                    }
                }

                $angkatan = $mahasiswa?->angkatan;
                if (! $angkatan && $mahasiswa?->nim_nip) {
                    $angkatan = '20'.substr((string) $mahasiswa->nim_nip, 2, 2);
                }

                $jadwalLamaHari = $exam?->tanggal ? $exam->tanggal->translatedFormat('l') : 'Senin';
                $jadwalLamaTgl = $exam?->tanggal ? $exam->tanggal->translatedFormat('d M Y') : '—';
                $jadwalLamaMulai = $exam?->jam_mulai ? substr($exam->jam_mulai, 0, 5) : '09:00';
                $jadwalLamaSelesai = $exam?->jam_selesai ? substr($exam->jam_selesai, 0, 5) : '10:30';

                $usulanHari = $req->tanggal_usulan ? $req->tanggal_usulan->translatedFormat('l') : 'Rabu';
                $usulanTgl = $req->tanggal_usulan ? $req->tanggal_usulan->translatedFormat('d M Y') : '—';
                $usulanMulai = $req->jam_mulai_usulan ? substr($req->jam_mulai_usulan, 0, 5) : '13:00';
                $usulanSelesai = $req->jam_selesai_usulan ? substr($req->jam_selesai_usulan, 0, 5) : '14:30';

                return [
                    'id' => $req->id,
                    'dosenId' => $pemohon?->id ?? 0,
                    'dosenNama' => $pemohon?->name ?? 'Dosen Pemohon',
                    'dosenNip' => $pemohon?->nim_nip ?? '—',
                    'dosenRole' => $dosenRole,
                    'mahasiswaId' => $mahasiswa?->id ?? 0,
                    'mahasiswaNama' => $mahasiswa?->name ?? 'Mahasiswa',
                    'mahasiswaNim' => $mahasiswa?->nim_nip ?? '—',
                    'mahasiswaAngkatan' => (string) ($angkatan ?? '2023'),
                    'tipeUjian' => $exam?->tipe ?? 'sempro',
                    'judulTa' => $exam?->catatan ?? 'Judul Tugas Akhir',
                    'jadwalAsli' => [
                        'hari' => $jadwalLamaHari,
                        'tanggal' => $jadwalLamaTgl,
                        'jamMulai' => $jadwalLamaMulai,
                        'jamSelesai' => $jadwalLamaSelesai,
                        'ruangan' => $exam?->link_online ?? 'Ruang Sidang FSTI 1',
                    ],
                    'jadwalUsulan' => [
                        'hari' => $usulanHari,
                        'tanggal' => $usulanTgl,
                        'jamMulai' => $usulanMulai,
                        'jamSelesai' => $usulanSelesai,
                        'ruangan' => $req->ruangan_usulan ?? 'Ruang Sidang FSTI 1',
                    ],
                    'alasan' => $req->alasan,
                    'catatanDosen' => $req->catatan ?? '-',
                    'tanggalPengajuan' => $req->created_at ? $req->created_at->translatedFormat('d M Y') : 'Hari ini',
                    'status' => $req->status,
                    'catatanKoorprodi' => $req->catatan_kaprodi,
                    'disetujuiAt' => $req->diputuskan_at ? $req->diputuskan_at->translatedFormat('d M Y, H:i') : null,
                    'disetujuiOleh' => $pemutus?->name,
                ];
            });

        return Inertia::render('Kaprodi/Persetujuan', [
            'dbJudul' => $judulList,
            'dbSempro' => $semproList,
            'dbSidang' => $sidangList,
            'dbReschedule' => $rescheduleList,
            'dosenOptions' => User::where('role', 'dosen')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function updateJudul(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:disetujui,ditolak,revisi'],
            'catatan_kaprodi' => ['nullable', 'string'],
            'pembimbing_1_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role', ['dosen', 'kaprodi', 'koordinator']);
                }),
            ],
            'pembimbing_2_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role', ['dosen', 'kaprodi', 'koordinator']);
                }),
            ],
        ]);

        $judul = PengajuanJudul::findOrFail($id);
        $user = Auth::user();
        $p1 = array_key_exists('pembimbing_1_id', $validated) ? $validated['pembimbing_1_id'] : $judul->pembimbing_1_id;
        $p2 = array_key_exists('pembimbing_2_id', $validated) ? $validated['pembimbing_2_id'] : $judul->pembimbing_2_id;
        if ($p1 && $p1 === $p2) {
            throw ValidationException::withMessages(['pembimbing_2_id' => 'Pembimbing harus berbeda.']);
        }
        if ($judul->status === 'draft') {
            throw ValidationException::withMessages(['status' => 'Draft belum diajukan.']);
        }

        $updateData = [
            'status' => $validated['status'],
            'catatan_kaprodi' => $validated['catatan_kaprodi'] ?? null,
            'disetujui_oleh' => $validated['status'] === 'disetujui' ? $user->id : null,
            'disetujui_at' => $validated['status'] === 'disetujui' ? now() : null,
        ];
        if (array_key_exists('pembimbing_1_id', $validated)) {
            $updateData['pembimbing_1_id'] = $validated['pembimbing_1_id'];
        }
        if (array_key_exists('pembimbing_2_id', $validated)) {
            $updateData['pembimbing_2_id'] = $validated['pembimbing_2_id'];
        }
        $judul->update($updateData);

        // Notify student of title approval / revision / rejection
        $statusIndo = match ($validated['status']) {
            'disetujui' => 'disetujui',
            'revisi' => 'diminta revisi',
            'ditolak' => 'ditolak',
            default => $validated['status'],
        };
        Notification::create([
            'user_id' => $judul->mahasiswa_id,
            'title' => 'Status Pengajuan Judul TA',
            'message' => "Pengajuan judul TA Anda '{$judul->judul_ta}' telah {$statusIndo} oleh Koordinator TA.".(! empty($validated['catatan_kaprodi']) ? " Catatan: {$validated['catatan_kaprodi']}" : ''),
            'type' => $validated['status'] === 'disetujui' ? 'success' : 'warning',
            'link' => '/mahasiswa/pengajuan-judul',
        ]);

        if (! empty($updateData['pembimbing_1_id'])) {
            Notification::create([
                'user_id' => $updateData['pembimbing_1_id'],
                'title' => 'Penugasan Pembimbing 1',
                'message' => "Anda telah ditugaskan sebagai Pembimbing 1 untuk mahasiswa {$judul->mahasiswa?->name} pada judul '{$judul->judul_ta}'.",
                'type' => 'info',
                'link' => '/dosen/bimbingan',
            ]);
        }
        if (! empty($updateData['pembimbing_2_id'])) {
            Notification::create([
                'user_id' => $updateData['pembimbing_2_id'],
                'title' => 'Penugasan Pembimbing 2',
                'message' => "Anda telah ditugaskan sebagai Pembimbing 2 untuk mahasiswa {$judul->mahasiswa?->name} pada judul '{$judul->judul_ta}'.",
                'type' => 'info',
                'link' => '/dosen/bimbingan',
            ]);
        }

        return redirect()->back()->with('success', 'Keputusan persetujuan judul berhasil disimpan!');
    }

    public function destroyJudul($id)
    {
        $judul = PengajuanJudul::findOrFail($id);
        $judul->delete();

        return redirect()->back()->with('success', 'Pengajuan judul berhasil dihapus!');
    }

    public function updateSempro(Request $request, $id)
    {
        $row = PendaftaranSempro::findOrFail($id);
        $validated = $request->validate([
            'status' => ['sometimes', 'in:menunggu,verifikasi_tendik,terjadwal,selesai,ditolak'],
            'status_akademik' => ['sometimes', 'in:menunggu,disetujui,revisi,ditolak'],
            'catatan_kaprodi' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
        ]);
        if (isset($validated['status']) && $validated['status'] !== $row->status) {
            throw ValidationException::withMessages(['status' => 'Status administrasi dan ujian bukan keputusan akademik.']);
        }
        if (in_array($row->status, ['terjadwal', 'selesai']) && isset($validated['status_akademik']) && $validated['status_akademik'] !== $row->status_akademik) {
            throw ValidationException::withMessages(['status_akademik' => 'Review jadwal aktif atau selesai tidak dapat diubah.']);
        }
        if (! array_key_exists('catatan_kaprodi', $validated) && array_key_exists('catatan', $validated)) {
            $validated['catatan_kaprodi'] = $validated['catatan'];
        }
        $row->update(array_intersect_key($validated, array_flip(['status_akademik', 'catatan_kaprodi'])));

        return redirect()->back()->with('success', 'Tinjauan akademik berhasil disimpan!');
    }

    public function updateSidang(Request $request, $id)
    {
        $row = PendaftaranSidang::findOrFail($id);
        $validated = $request->validate([
            'status' => ['sometimes', 'in:menunggu,verifikasi_tendik,terjadwal,selesai,ditolak'],
            'status_akademik' => ['sometimes', 'in:menunggu,disetujui,revisi,ditolak'],
            'catatan_kaprodi' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
        ]);
        if (isset($validated['status']) && $validated['status'] !== $row->status) {
            throw ValidationException::withMessages(['status' => 'Status administrasi dan ujian bukan keputusan akademik.']);
        }
        if (in_array($row->status, ['terjadwal', 'selesai']) && isset($validated['status_akademik']) && $validated['status_akademik'] !== $row->status_akademik) {
            throw ValidationException::withMessages(['status_akademik' => 'Review jadwal aktif atau selesai tidak dapat diubah.']);
        }
        if (! array_key_exists('catatan_kaprodi', $validated) && array_key_exists('catatan', $validated)) {
            $validated['catatan_kaprodi'] = $validated['catatan'];
        }
        $row->update(array_intersect_key($validated, array_flip(['status_akademik', 'catatan_kaprodi'])));

        return redirect()->back()->with('success', 'Tinjauan akademik berhasil disimpan!');
    }
}
