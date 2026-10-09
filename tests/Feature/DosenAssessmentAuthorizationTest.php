<?php

namespace Tests\Feature;

use App\Models\Penjadwalan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DosenAssessmentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('unauthorizedAssignments')]
    public function test_unassigned_or_supervisor_only_lecturers_cannot_change_assessment(?string $assignment): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $schedule = $this->schedule($assignment ? [$assignment => $dosen->id] : []);
        $before = $schedule->fresh()->getAttributes();

        $this->actingAs($dosen)->post("/dosen/penilaian/{$schedule->id}", [
            'nilai' => 80,
            'catatan' => 'Unauthorized change',
        ])->assertNotFound();

        $this->assertSame($before, $schedule->fresh()->getAttributes());
    }

    public static function unauthorizedAssignments(): array
    {
        return [[null], ['pembimbing_1_id'], ['pembimbing_2_id']];
    }

    #[DataProvider('examinerAssignments')]
    public function test_each_assigned_examiner_can_update_assessment(string $assignment): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $schedule = $this->schedule([$assignment => $dosen->id]);

        $this->actingAs($dosen)->post("/dosen/penilaian/{$schedule->id}", [
            'nilai' => 80,
            'catatan' => 'Authorized evaluation',
        ])->assertRedirect();

        $this->assertDatabaseHas('penjadwalan', [
            'id' => $schedule->id, 'status' => 'selesai', 'catatan' => 'Authorized evaluation',
        ]);
    }

    public static function examinerAssignments(): array
    {
        return [['penguji_1_id'], ['penguji_2_id']];
    }

    public function test_nonexistent_schedule_is_not_found(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $this->actingAs($dosen)->post('/dosen/penilaian/999999', ['nilai' => 80])->assertNotFound();
        $this->assertDatabaseCount('penjadwalan', 0);
    }

    public function test_student_cannot_use_assessment_write_route_even_if_assigned(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $schedule = $this->schedule(['penguji_1_id' => $student->id]);
        $before = $schedule->fresh()->getAttributes();

        $this->actingAs($student)->post("/dosen/penilaian/{$schedule->id}", ['nilai' => 80])
            ->assertRedirect('/dashboard');

        $this->assertSame($before, $schedule->fresh()->getAttributes());
    }

    private function schedule(array $assignments = []): Penjadwalan
    {
        return Penjadwalan::create(array_merge([
            'tipe' => 'sempro',
            'pendaftaran_id' => 1,
            'mahasiswa_id' => User::factory()->create(['role' => 'mahasiswa'])->id,
            'tanggal' => '2026-09-28',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:00:00',
            'status' => 'terpublikasi',
            'catatan' => 'Original evaluation',
        ], $assignments));
    }
}
