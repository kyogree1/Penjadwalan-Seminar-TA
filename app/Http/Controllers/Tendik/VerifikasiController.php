<?php

namespace App\Http\Controllers\Tendik;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VerifikasiController extends Controller
{
    public function index(): Response
    {
        $semproList = PendaftaranSempro::with('mahasiswa:id,name,username,nim_nip,prodi')
            ->latest()
            ->get();

        $sidangList = PendaftaranSidang::with('mahasiswa:id,name,username,nim_nip,prodi')
            ->latest()
            ->get();

        return Inertia::render('Tendik/Verifikasi', [
            'pengajuan' => $semproList->map(fn ($row) => $this->serialize($row, 'sempro'))
                ->concat($sidangList->map(fn ($row) => $this->serialize($row, 'sidang')))->values(),
        ]);
    }

    private function serialize(PendaftaranSempro|PendaftaranSidang $row, string $type): array
    {
        $fields = $type === 'sempro'
            ? ['lembar_kehadiran_file' => 'Lembar Kehadiran', 'proposal_file' => 'Proposal', 'turnitin_file' => 'Turnitin', 'iaet_file' => 'Skor IAET']
            : ['skor_iaet_file' => 'Skor IAET', 'draft_laporan_file' => 'Draft Laporan', 'turnitin_file' => 'Turnitin'];

        return [
            'id' => $row->id,
            'tipe' => $type,
            'nama' => $row->mahasiswa?->name,
            'nim' => $row->mahasiswa?->nim_nip,
            'judul' => $row->judul_ta,
            'tanggalDaftar' => $row->created_at?->toISOString(),
            'status' => $row->status,
            'catatan' => $row->catatan,
            'dokumen' => collect($fields)->map(fn ($label, $field) => [
                'label' => $label, 'path' => $row->{$field},
            ])->values()->all(),
        ];
    }

    public function verifySempro(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:verifikasi_tendik,ditolak,menunggu'],
            'catatan' => ['nullable', 'string'],
        ]);

        $sempro = PendaftaranSempro::findOrFail($id);
        if (in_array($sempro->status, ['terjadwal', 'selesai'])) {
            throw ValidationException::withMessages(['status' => 'Pendaftaran terjadwal atau selesai tidak dapat diverifikasi ulang.']);
        }
        $sempro->update($validated);

        return redirect()->back()->with('success', 'Verifikasi berkas Seminar Proposal berhasil disimpan!');
    }

    public function verifySidang(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:verifikasi_tendik,ditolak,menunggu'],
            'catatan' => ['nullable', 'string'],
        ]);

        $sidang = PendaftaranSidang::findOrFail($id);
        if (in_array($sidang->status, ['terjadwal', 'selesai'])) {
            throw ValidationException::withMessages(['status' => 'Pendaftaran terjadwal atau selesai tidak dapat diverifikasi ulang.']);
        }
        $sidang->update($validated);

        return redirect()->back()->with('success', 'Verifikasi berkas Sidang TA berhasil disimpan!');
    }
}
