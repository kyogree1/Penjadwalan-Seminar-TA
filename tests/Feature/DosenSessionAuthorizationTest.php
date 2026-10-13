<?php

namespace Tests\Feature;

use App\Models\Bimbingan;
use App\Models\PengajuanJudul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DosenSessionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('invalidRelationships')]
    public function test_session_creation_requires_a_student_with_an_approved_supervisor_assignment(string $case): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $target = User::factory()->create(['role' => $case === 'wrong_role' ? 'tendik' : 'mahasiswa']);
        if ($case !== 'no_assignment') {
            $this->assignment($target, $case === 'other_supervisor' ? User::factory()->create(['role' => 'dosen']) : $dosen,
                'pembimbing_1_id', in_array($case, ['draft', 'menunggu', 'ditolak']) ? $case : 'disetujui');
        }

        $this->actingAs($dosen)->post('/dosen/bimbingan/sesi', $this->payload($target->id))->assertForbidden();

        $this->assertDatabaseCount('bimbingan', 0);
    }

    public static function invalidRelationships(): array
    {
        return [['no_assignment'], ['other_supervisor'], ['wrong_role'], ['draft'], ['menunggu'], ['ditolak']];
    }

    #[DataProvider('supervisorAssignments')]
    public function test_each_approved_supervisor_can_create_and_paraf_a_session(string $slot): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $this->assignment($student, $dosen, $slot);

        $this->actingAs($dosen)->post('/dosen/bimbingan/sesi', $this->payload($student->id))->assertRedirect();
        $this->assertDatabaseHas('bimbingan', [
            'mahasiswa_id' => $student->id, 'dosen_id' => $dosen->id, 'status' => 'delivered',
        ]);
        $session = Bimbingan::sole();
        $this->patch("/dosen/bimbingan/{$session->id}/paraf", ['status' => 'revisi', 'catatan_dosen' => 'Revise chapter'])
            ->assertRedirect();
        $this->assertDatabaseHas('bimbingan', ['id' => $session->id, 'status' => 'process', 'catatan_dosen' => 'Revise chapter']);
    }

    public static function supervisorAssignments(): array
    {
        return [['pembimbing_1_id'], ['pembimbing_2_id']];
    }

    public function test_missing_student_fails_validation_without_creating_a_session(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $this->actingAs($dosen)->post('/dosen/bimbingan/sesi', $this->payload(999999))
            ->assertSessionHasErrors('mahasiswa_id');
        $this->assertDatabaseCount('bimbingan', 0);
    }

    public function test_student_cannot_use_lecturer_session_creation_route(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $this->actingAs($student)->post('/dosen/bimbingan/sesi', $this->payload($student->id))
            ->assertRedirect('/dashboard');
        $this->assertDatabaseCount('bimbingan', 0);
    }

    public function test_other_lecturer_cannot_paraf_a_session(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $session = Bimbingan::create(array_merge($this->payload(User::factory()->create(['role' => 'mahasiswa'])->id), [
            'dosen_id' => User::factory()->create(['role' => 'dosen'])->id,
            'keterangan' => 'Offline', 'status' => 'process',
        ]));
        $before = $session->fresh()->getAttributes();

        $this->actingAs($dosen)->patch("/dosen/bimbingan/{$session->id}/paraf", ['status' => 'disetujui'])
            ->assertNotFound();
        $this->assertSame($before, $session->fresh()->getAttributes());
    }

    private function assignment(User $student, User $dosen, string $slot, string $status = 'disetujui'): void
    {
        PengajuanJudul::create([
            'mahasiswa_id' => $student->id, 'judul_ta' => 'Assignment test',
            'bidang_penelitian' => 'Sistem Cerdas', $slot => $dosen->id, 'status' => $status,
        ]);
    }

    private function payload(int $studentId): array
    {
        return ['mahasiswa_id' => $studentId, 'tanggal' => '2026-09-28', 'rangkuman' => 'Discuss chapter'];
    }
}
