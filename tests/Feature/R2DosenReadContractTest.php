<?php

namespace Tests\Feature;

use App\Models\Bimbingan;
use App\Models\Penjadwalan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class R2DosenReadContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_read_contract_scopes_all_assignment_positions_and_preserves_status_without_scores(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $other = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa', 'nim_nip' => 'REAL-NIM', 'username' => 'login-only']);
        foreach (['pembimbing_1_id', 'pembimbing_2_id', 'penguji_1_id', 'penguji_2_id'] as $position) {
            Penjadwalan::create(['tipe' => 'sidang', 'pendaftaran_id' => 1, 'mahasiswa_id' => $student->id, $position => $dosen->id, 'tanggal' => '2026-10-10', 'jam_mulai' => '09:00', 'jam_selesai' => '10:00', 'status' => 'draft']);
        }
        Penjadwalan::create(['tipe' => 'sempro', 'pendaftaran_id' => 1, 'mahasiswa_id' => $student->id, 'penguji_1_id' => $other->id, 'tanggal' => '2026-10-10', 'jam_mulai' => '09:00', 'jam_selesai' => '10:00']);
        $this->actingAs($dosen)->get('/dosen/penilaian')->assertInertia(fn (Assert $page) => $page
            ->component('Dosen/Penilaian')->has('dbJadwalUji', 4)->where('dbJadwalUji.0.mahasiswa.nim_nip', 'REAL-NIM')->where('dbJadwalUji.0.status', 'draft')->missing('dbJadwalUji.0.nilai'));
        Penjadwalan::query()->delete();
        $this->get('/dosen/penilaian')->assertInertia(fn (Assert $page) => $page->where('dbJadwalUji', []));
    }

    public function test_student_logbook_rejects_unknown_or_non_lecturer_instead_of_substituting_first_lecturer(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        User::factory()->create(['role' => 'dosen']);
        foreach (['Unknown lecturer', $student->id] as $dosen) {
            $this->actingAs($student)->post('/pendaftaran/bimbingan', ['tanggal' => '2026-10-10', 'dosen' => $dosen, 'rangkuman' => 'Actual', 'keterangan' => 'Topic'])->assertSessionHasErrors('dosen');
        }
        $this->assertDatabaseCount('bimbingan', 0);
    }

    public function test_title_selection_rejects_non_lecturer_database_ids(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $this->actingAs($student)->post('/pendaftaran/judul', ['judul_ta' => 'Actual', 'bidang_penelitian' => 'Informatika', 'pembimbing_1' => $student->id])->assertSessionHasErrors('pembimbing_1');
        $this->assertDatabaseCount('pengajuan_judul', 0);
    }

    public function test_profile_updates_only_supported_fields_and_returns_server_feedback(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Original']);
        $this->actingAs($user)->from('/profile')->patch('/profile', ['name' => 'Updated', 'email' => 'updated@example.test', 'role' => 'kaprodi', 'phone' => 'unsupported'])
            ->assertRedirect('/profile')->assertSessionHas('success');
        $this->assertSame('Updated', $user->fresh()->name);
        $this->assertSame('mahasiswa', $user->fresh()->role);
        $this->get('/profile')->assertInertia(fn (Assert $page) => $page->component('Profile/Index')->where('user.name', 'Updated')->missing('user.password')->missing('user.phone'));
        $this->patch('/profile', ['name' => '', 'email' => 'bad'])->assertSessionHasErrors(['name', 'email']);
        $this->assertSame('Updated', $user->fresh()->name);
    }

    public function test_dashboard_counts_real_pending_sessions_and_published_future_examiner_duties(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $other = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        foreach ([$dosen, $other] as $lecturer) {
            Bimbingan::create(['mahasiswa_id' => $student->id, 'dosen_id' => $lecturer->id, 'tanggal' => now()->toDateString(), 'rangkuman' => 'Aktual', 'keterangan' => 'Topik', 'status' => 'process']);
        }
        foreach (['draft', 'terpublikasi'] as $status) {
            Penjadwalan::create(['tipe' => 'sempro', 'pendaftaran_id' => 1, 'mahasiswa_id' => $student->id, 'penguji_1_id' => $dosen->id, 'tanggal' => now()->addDay()->toDateString(), 'jam_mulai' => '09:00', 'jam_selesai' => '10:00', 'status' => $status]);
        }
        $this->actingAs($dosen)->get('/dosen/dashboard')->assertInertia(fn (Assert $page) => $page
            ->component('Dosen/Dashboard')->where('stats.pendingBimbingan', 1)->where('stats.jadwalUjiCount', 1));
    }
}
