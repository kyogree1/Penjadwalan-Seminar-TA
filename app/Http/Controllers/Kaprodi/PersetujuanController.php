<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
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
                $semproArr['penguji1'] = $jadwal?->penguji1?->name;
                $semproArr['penguji2'] = $jadwal?->penguji2?->name;
                $semproArr['pembimbing1'] = $jadwal?->pembimbing1?->name;
                $semproArr['pembimbing2'] = $jadwal?->pembimbing2?->name;
                $semproArr['tanggal'] = $jadwal?->tanggal ? $jadwal->tanggal->translatedFormat('l, d M Y') : null;
                $semproArr['waktu'] = $jadwal ? (substr($jadwal->jam_mulai, 0, 5) . ' - ' . substr($jadwal->jam_selesai, 0, 5)) : null;
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
                $sidangArr['nilai'] = $nilaiHuruf ?? ($nilaiAvg ? (string) $nilaiAvg : null);
                $sidangArr['nilai_angka'] = $nilaiAvg;
                $sidangArr['jumlah_penilai'] = $penilaian->count();
                $sidangArr['penguji1'] = $jadwal?->penguji1?->name;
                $sidangArr['penguji2'] = $jadwal?->penguji2?->name;
                $sidangArr['pembimbing1'] = $jadwal?->pembimbing1?->name;
                $sidangArr['pembimbing2'] = $jadwal?->pembimbing2?->name;
                $sidangArr['tanggal'] = $jadwal?->tanggal ? $jadwal->tanggal->translatedFormat('l, d M Y') : null;
                $sidangArr['waktu'] = $jadwal ? (substr($jadwal->jam_mulai, 0, 5) . ' - ' . substr($jadwal->jam_selesai, 0, 5)) : null;

                return $sidangArr;
            });

        return Inertia::render('Kaprodi/Persetujuan', [
            'dbJudul' => $judulList,
            'dbSempro' => $semproList,
            'dbSidang' => $sidangList,
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
