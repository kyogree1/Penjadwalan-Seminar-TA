<?php

namespace Tests\Feature;

use App\Models\Bimbingan;
use App\Models\PengajuanJudul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DosenBimbinganTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_contract_includes_assigned_students_without_sessions_and_only_own_logs(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $other = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['name' => 'A Aktual', 'nim_nip' => '456']);
        $empty = User::factory()->create(['name' => 'B Tanpa Sesi', 'username' => 'fallback']);
        $outside = User::factory()->create();
        foreach ([[$student, $dosen, 'disetujui'], [$empty, $dosen, 'disetujui'], [$outside, $other, 'disetujui'], [$outside, $dosen, 'menunggu']] as [$s, $d, $status]) {
            PengajuanJudul::create(['mahasiswa_id' => $s->id, 'judul_ta' => 'Judul Aktual', 'bidang_penelitian' => 'IF', 'pembimbing_2_id' => $d->id, 'status' => $status]);
        }
        $session = Bimbingan::create(['mahasiswa_id' => $student->id, 'dosen_id' => $dosen->id, 'tanggal' => '2026-10-01', 'rangkuman' => 'Pembahasan asli', 'keterangan' => 'Bab 2', 'status' => 'process']);
        Bimbingan::create(['mahasiswa_id' => $student->id, 'dosen_id' => $other->id, 'tanggal' => '2026-10-02', 'rangkuman' => 'Rahasia dosen lain', 'keterangan' => 'Online', 'status' => 'delivered']);
        $this->actingAs($dosen)->get('/dosen/bimbingan')->assertInertia(fn (Assert $page) => $page
            ->component('Dosen/Bimbingan')->missing('dbBimbinganList')->has('advisees', 2)
            ->where('advisees.0.id', $student->id)->where('advisees.0.nim', '456')
            ->where('advisees.0.roleAs', 'Pembimbing 2')->has('advisees.0.sesiList', 1)
            ->where('advisees.0.sesiList.0.id', $session->id)->where('advisees.0.sesiList.0.tanggal', '2026-10-01')
            ->where('advisees.0.sesiList.0.status', 'process')->where('advisees.0.sesiList.0.keterangan', 'Bab 2')
            ->where('advisees.1.nim', 'fallback')->where('advisees.1.sesiList', []));
    }

    public function test_chapter_topic_must_fit_the_existing_persisted_field(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create();
        PengajuanJudul::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Judul', 'bidang_penelitian' => 'IF', 'pembimbing_1_id' => $dosen->id, 'status' => 'disetujui']);
        $this->actingAs($dosen)->post('/dosen/bimbingan/sesi', ['mahasiswa_id' => $student->id, 'tanggal' => '2026-10-01', 'rangkuman' => 'Isi', 'keterangan' => str_repeat('x', 256)])->assertSessionHasErrors('keterangan');
        $this->assertDatabaseCount('bimbingan', 0);
    }

    public function test_empty_contract_has_no_fixture_records(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'dosen']))->get('/dosen/bimbingan')
            ->assertInertia(fn (Assert $page) => $page->where('advisees', []));
    }

    public function test_failed_and_denied_mutations_do_not_change_persisted_sessions(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create();
        $other = User::factory()->create(['role' => 'dosen']);
        $session = Bimbingan::create(['mahasiswa_id' => $student->id, 'dosen_id' => $other->id, 'tanggal' => '2026-10-01', 'rangkuman' => 'Asli', 'keterangan' => 'Online', 'status' => 'process']);
        $this->actingAs($dosen)->patch("/dosen/bimbingan/{$session->id}/paraf", ['status' => 'delivered'])->assertNotFound();
        $this->post('/dosen/bimbingan/sesi', ['mahasiswa_id' => $student->id, 'tanggal' => '2026-10-01', 'rangkuman' => 'Tidak sah'])->assertForbidden();
        $this->post('/dosen/bimbingan/sesi', ['mahasiswa_id' => $student->id])->assertSessionHasErrors(['tanggal', 'rangkuman']);
        $this->assertDatabaseCount('bimbingan', 1);
        $this->assertSame('process', $session->fresh()->status);
        $this->actingAs($other)->patch("/dosen/bimbingan/{$session->id}/paraf", ['status' => 'invalid'])->assertSessionHasErrors('status');
        $this->assertSame('process', $session->fresh()->status);
    }
}
