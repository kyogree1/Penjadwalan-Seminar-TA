<?php

namespace App\Http\Controllers\Tendik;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\Penjadwalan;
use App\Models\SchedulePeriod;
use Inertia\Inertia;
use Inertia\Response;

class ArsipController extends Controller
{
    public function index(): Response
    {
        // ── Ringkasan Sempro ──────────────────────────────────────────
        $totalSelesaiSempro = PendaftaranSempro::where('status', 'selesai')->count();

        // ── Ringkasan Sidang ──────────────────────────────────────────
        $totalSelesaiSidang = PendaftaranSidang::where('status', 'selesai')->count();

        // ── Total mahasiswa yang sudah lulus (sidang selesai) ─────────
        // Satu mahasiswa bisa dihitung sekali (distinct mahasiswa_id)
        $totalLulus = PendaftaranSidang::where('status', 'selesai')
            ->distinct('mahasiswa_id')
            ->count('mahasiswa_id');

        // ── Periode terakhir aktif ────────────────────────────────────
        $periodeTerakhir = SchedulePeriod::orderByDesc('start_date')
            ->value('name');

        // ── Riwayat sempro selesai (untuk tabel arsip) ───────────────
        $arsipSempro = PendaftaranSempro::with('mahasiswa:id,name,username,nim_nip,prodi')
            ->where('status', 'selesai')
            ->latest()
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'tipe' => 'Seminar Proposal',
                'mahasiswa' => $row->mahasiswa?->name,
                'nim' => $row->mahasiswa?->username ?? $row->mahasiswa?->nim_nip,
                'prodi' => $row->mahasiswa?->prodi,
                'judul' => $row->judul_ta,
                'tanggal_selesai' => $row->updated_at?->format('d M Y'),
            ]);

        // ── Riwayat sidang selesai (untuk tabel arsip) ───────────────
        $arsipSidang = PendaftaranSidang::with('mahasiswa:id,name,username,nim_nip,prodi')
            ->where('status', 'selesai')
            ->latest()
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'tipe' => 'Sidang Tugas Akhir',
                'mahasiswa' => $row->mahasiswa?->name,
                'nim' => $row->mahasiswa?->username ?? $row->mahasiswa?->nim_nip,
                'prodi' => $row->mahasiswa?->prodi,
                'judul' => $row->judul_ta,
                'tanggal_selesai' => $row->updated_at?->format('d M Y'),
            ]);

        // ── Gabungkan & urutkan berdasarkan tanggal terbaru ──────────
        $arsipList = $arsipSempro->merge($arsipSidang)
            ->sortByDesc('tanggal_selesai')
            ->values();

        // ── Kapasitas fitur arsip ─────────────────────────────────────
        // persistence: true  → data sudah tersimpan dari DB (bukan placeholder)
        // printing: false    → fitur cetak/unduh dokumen belum diimplementasi
        $archiveCapabilities = [
            'persistence' => true,
            'printing' => false,
        ];

        return Inertia::render('Tendik/Arsip', [
            'archiveCapabilities' => $archiveCapabilities,
            'summary' => [
                'totalSelesaiSempro' => $totalSelesaiSempro,
                'totalSelesaiSidang' => $totalSelesaiSidang,
                'totalLulus' => $totalLulus,
                'periodeTerakhir' => $periodeTerakhir,
            ],
            'arsipList' => $arsipList,
        ]);
    }
}
