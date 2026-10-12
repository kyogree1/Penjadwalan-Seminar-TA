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
        $judulList = PengajuanJudul::with([
            'mahasiswa:id,name,username,nim_nip',
            'pembimbing1:id,name',
            'pembimbing2:id,name',
        ])
            ->latest()
            ->get();

        $semproList = PendaftaranSempro::with('mahasiswa:id,name,username,nim_nip')
            ->latest()
            ->get();

        $sidangList = PendaftaranSidang::with('mahasiswa:id,name,username,nim_nip')
            ->latest()
            ->get();

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
            'pembimbing_1_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'dosen')],
            'pembimbing_2_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'dosen')],
        ]);

        $judul = PengajuanJudul::findOrFail($id);
        $user = Auth::user();
        $p1 = $validated['pembimbing_1_id'] ?? $judul->pembimbing_1_id;
        $p2 = $validated['pembimbing_2_id'] ?? $judul->pembimbing_2_id;
        if ($p1 && $p1 === $p2) {
            throw ValidationException::withMessages(['pembimbing_2_id' => 'Pembimbing harus berbeda.']);
        }
        if ($judul->status === 'draft') {
            throw ValidationException::withMessages(['status' => 'Draft belum diajukan.']);
        }

        $judul->update([
            'status' => $validated['status'],
            'catatan_kaprodi' => $validated['catatan_kaprodi'] ?? null,
            'disetujui_oleh' => $validated['status'] === 'disetujui' ? $user->id : null,
            'disetujui_at' => $validated['status'] === 'disetujui' ? now() : null,
            'pembimbing_1_id' => $validated['pembimbing_1_id'] ?? $judul->pembimbing_1_id,
            'pembimbing_2_id' => $validated['pembimbing_2_id'] ?? $judul->pembimbing_2_id,
        ]);

        return redirect()->back()->with('success', 'Keputusan persetujuan judul berhasil disimpan!');
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
