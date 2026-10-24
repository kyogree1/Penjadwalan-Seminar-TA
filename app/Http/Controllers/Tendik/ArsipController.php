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
        $arsipList = $arsipSempro->concat($arsipSidang)
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

    /**
     * Ekspor data arsip kelulusan sempro & sidang ke CSV.
     */
    public function exportCsv()
    {
        $arsipSempro = PendaftaranSempro::with('mahasiswa:id,name,username,nim_nip,prodi')
            ->where('status', 'selesai')
            ->latest()
            ->get();

        $arsipSidang = PendaftaranSidang::with('mahasiswa:id,name,username,nim_nip,prodi')
            ->where('status', 'selesai')
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($arsipSempro, $arsipSidang) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No', 'Tipe Ujian', 'NIM', 'Nama Mahasiswa', 'Program Studi', 'Judul Tugas Akhir', 'Tanggal Selesai']);

            $no = 1;
            foreach ($arsipSempro as $row) {
                fputcsv($handle, [
                    $no++,
                    'Seminar Proposal',
                    $row->mahasiswa?->nim_nip ?? $row->mahasiswa?->username ?? '-',
                    $row->mahasiswa?->name ?? '-',
                    $row->mahasiswa?->prodi ?? 'Informatika',
                    $row->judul_ta,
                    $row->updated_at?->format('Y-m-d') ?? '-',
                ]);
            }
            foreach ($arsipSidang as $row) {
                fputcsv($handle, [
                    $no++,
                    'Sidang Tugas Akhir',
                    $row->mahasiswa?->nim_nip ?? $row->mahasiswa?->username ?? '-',
                    $row->mahasiswa?->name ?? '-',
                    $row->mahasiswa?->prodi ?? 'Informatika',
                    $row->judul_ta,
                    $row->updated_at?->format('Y-m-d') ?? '-',
                ]);
            }
            fclose($handle);
        }, 'arsip-kelulusan-ta-' . date('Ymd-His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
