<?php

namespace Tests\Feature;

use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\Penjadwalan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class KaprodiDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_preview_is_bounded_and_queries_do_not_grow_with_lecturers(): void
    {
        $kaprodi = User::factory()->create(['role' => 'kaprodi']);
        $student = User::factory()->create(['role' => 'mahasiswa', 'username' => 'fallback-nim']);
        $dosen = User::factory()->create(['role' => 'dosen']);
        $ids = [];
        for ($i = 0; $i < 7; $i++) {
            $ids[] = PengajuanJudul::create([
                'mahasiswa_id' => $student->id, 'judul_ta' => "Judul $i", 'bidang_penelitian' => 'Informatika',
                'status' => 'menunggu', 'pembimbing_1_id' => $dosen->id,
            ])->id;
        }
        DB::enableQueryLog();
        $this->actingAs($kaprodi)->get('/kaprodi/dashboard')->assertInertia(fn (Assert $page) => $page
            ->where('pendingCounts.judul', 7)
            ->has('pendingApprovals', 5)
            ->where('pendingApprovals.0.id', $ids[0])
            ->where('pendingApprovals.4.id', $ids[4])
            ->where('pendingApprovals.0.nim', 'fallback-nim')
            ->where('lecturers.0.total', 0));
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();
        User::factory()->count(20)->create(['role' => 'dosen']);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get('/kaprodi/dashboard')->assertInertia(fn (Assert $page) => $page->has('lecturers', 21));
        $this->assertSame($queries, count(DB::getQueryLog()));
        DB::disableQueryLog();

        PengajuanJudul::whereIn('id', $ids)->update(['status' => 'disetujui']);
        $this->get('/kaprodi/dashboard')->assertInertia(fn (Assert $page) => $page
            ->where('pendingCounts.judul', 0)
            ->where('stats.antreanPersetujuan', 0)
            ->where('pendingApprovals', []));
    }

    public function test_empty_dashboard_returns_zero_counts_empty_lists_and_unsupported_deadlines(): void
    {
        $kaprodi = User::factory()->create(['role' => 'koordinator']);
        $this->actingAs($kaprodi)->get('/kaprodi/dashboard')->assertInertia(fn (Assert $page) => $page
            ->component('Kaprodi/Dashboard')
            ->where('stats', ['totalDosen' => 0, 'totalMahasiswa' => 0, 'antreanPersetujuan' => 0])
            ->where('pendingCounts', ['judul' => 0, 'sempro' => 0, 'sidang' => 0])
            ->where('lecturers', [])
            ->where('pendingApprovals', [])
            ->where('deadlines', null));
    }

    public function test_dashboard_reports_actual_pending_records_and_approved_supervision(): void
    {
        $kaprodi = User::factory()->create(['role' => 'kaprodi']);
        $dosen = User::factory()->create(['role' => 'dosen', 'name' => 'Dosen Aktual', 'nim_nip' => '123']);
        $idle = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa', 'nim_nip' => '456']);
        $other = User::factory()->create(['role' => 'mahasiswa']);
        $title = ['mahasiswa_id' => $student->id, 'judul_ta' => 'Judul Aktual', 'bidang_penelitian' => 'Informatika'];
        $pending = PengajuanJudul::create($title + ['status' => 'menunggu', 'pembimbing_1_id' => $idle->id]);
        // Repeated approved records and both positions must not double-count students.
        PengajuanJudul::create($title + ['status' => 'disetujui', 'pembimbing_1_id' => $dosen->id, 'pembimbing_2_id' => $dosen->id]);
        PengajuanJudul::create($title + ['status' => 'disetujui', 'pembimbing_1_id' => $dosen->id]);
        PengajuanJudul::create(array_replace($title, ['mahasiswa_id' => $other->id, 'status' => 'ditolak', 'pembimbing_1_id' => $dosen->id]));
        PendaftaranSempro::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Sempro', 'bentuk_ta' => 'Skripsi', 'status' => 'menunggu']);
        PendaftaranSempro::create(['mahasiswa_id' => $other->id, 'judul_ta' => 'Selesai bukan kelulusan', 'bentuk_ta' => 'Skripsi', 'status' => 'selesai']);
        PendaftaranSidang::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Sidang', 'status' => 'menunggu']);

        Penjadwalan::create([
            'tipe' => 'sempro', 'pendaftaran_id' => 1, 'mahasiswa_id' => $other->id,
            'penguji_1_id' => $idle->id, 'penguji_2_id' => $dosen->id,
            'tanggal' => '2026-10-10', 'jam_mulai' => '09:00', 'jam_selesai' => '10:00',
            'status' => 'terpublikasi',
        ]);

        $this->actingAs($kaprodi)->get('/kaprodi/dashboard')->assertInertia(fn (Assert $page) => $page
            ->component('Kaprodi/Dashboard')
            ->where('stats', ['totalDosen' => 2, 'totalMahasiswa' => 2, 'antreanPersetujuan' => 3])
            ->where('pendingCounts', ['judul' => 1, 'sempro' => 1, 'sidang' => 1])
            ->has('lecturers', 2)
            ->where('lecturers.0', ['id' => $dosen->id, 'name' => 'Dosen Aktual', 'nip' => '123', 'bimbingan1' => 1, 'bimbingan2' => 1, 'total' => 1])
            ->where('lecturers.1.total', 0)
            ->has('pendingApprovals', 1)
            ->where('pendingApprovals.0.id', $pending->id)
            ->where('pendingApprovals.0.nama', $student->name)
            ->where('pendingApprovals.0.nim', '456')
            ->where('pendingApprovals.0.judul', 'Judul Aktual')
            ->where('pendingApprovals.0.pembimbing1', $idle->name)
            ->where('pendingApprovals.0.pembimbing2', null)
            ->where('deadlines', null));
    }
}
