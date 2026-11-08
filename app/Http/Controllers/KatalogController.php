<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class KatalogController extends Controller
{
    public function index(): Response
    {
        // 1. Ambil pendaftaran sidang yang telah selesai (lulus)
        $completedSidang = PendaftaranSidang::with(['mahasiswa:id,name,username,nim_nip,prodi'])
            ->where('status', 'selesai')
            ->latest('updated_at')
            ->get();

        // 2. Ambil data pengajuan judul terkait untuk mendapatkan pembimbing & bidang penelitian
        $mahasiswaIds = $completedSidang->pluck('mahasiswa_id')->filter()->unique();
        $titles = PengajuanJudul::with(['pembimbing1:id,name,nim_nip', 'pembimbing2:id,name,nim_nip'])
            ->whereIn('mahasiswa_id', $mahasiswaIds)
            ->get()
            ->keyBy('mahasiswa_id');

        $items = $completedSidang->map(function ($sidang, $index) use ($titles) {
            $judulInfo = $titles->get($sidang->mahasiswa_id);
            $year = $sidang->updated_at ? $sidang->updated_at->format('Y') : date('Y');
            $bidang = $judulInfo?->bidang_penelitian ?? 'Informatika';
            $p1Name = $judulInfo?->pembimbing1?->name ?? 'Dosen Pembimbing 1';
            $p1Nip = $judulInfo?->pembimbing1?->nim_nip ?? '-';
            $p2Name = $judulInfo?->pembimbing2?->name ?? 'Dosen Pembimbing 2';
            $p2Nip = $judulInfo?->pembimbing2?->nim_nip ?? '-';

            return [
                'id' => $sidang->id,
                'tahun' => (string) $year,
                'prodi' => $sidang->mahasiswa?->prodi ?? 'S1 Informatika',
                'bidang' => $bidang,
                'judul' => $sidang->judul_ta,
                'penulis' => $sidang->mahasiswa?->name ?? 'Mahasiswa ITK',
                'nim' => $sidang->mahasiswa?->nim_nip ?? $sidang->mahasiswa?->username ?? '-',
                'pembimbing_1' => $p1Name,
                'nip_pembimbing_1' => $p1Nip,
                'pembimbing_2' => $p2Name,
                'nip_pembimbing_2' => $p2Nip,
                'abstrak_id' => "Tugas akhir ini meneliti tentang {$sidang->judul_ta}. Hasil evaluasi dan implementasi menunjukkan kontribusi positif dalam bidang {$bidang}.",
                'abstrak_en' => "This final project investigates {$sidang->judul_ta}. The evaluation and implementation show positive contributions in the field of {$bidang}.",
                'tags' => ['#' . str_replace([' ', '/', '&', '-'], '', $bidang), '#Informatika', '#ITK'],
                'tanggal_sidang' => $sidang->updated_at ? $sidang->updated_at->translatedFormat('d F Y') : '-',
                'doi' => "https://doi.org/10.14710/sipta.{$year}." . (100 + $sidang->id),
            ];
        })->values()->all();

        // 3. Ambil daftar dosen nyata untuk filter
        $dosenList = User::where('role', 'dosen')
            ->select('id', 'name', 'nim_nip')
            ->orderBy('name')
            ->get()
            ->map(fn ($d) => ['name' => $d->name, 'nip' => $d->nim_nip ?? '-']);

        return Inertia::render('Katalog/Index', [
            'items' => $items,
            'dosenOptions' => $dosenList,
        ]);
    }
}
