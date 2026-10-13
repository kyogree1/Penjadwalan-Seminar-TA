<?php

namespace Tests\Feature;

use App\Models\PendaftaranSempro;
use App\Models\Penjadwalan;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class R3AssessmentTest extends TestCase
{
    use RefreshDatabase;

    private function exam(array $attributes = []): array
    {
        $first = User::factory()->create(['role' => 'dosen']);
        $second = User::factory()->create(['role' => 'dosen']);
        $exam = Penjadwalan::create(array_merge([
            'tipe' => 'sempro', 'pendaftaran_id' => 1,
            'mahasiswa_id' => User::factory()->create(['role' => 'mahasiswa'])->id,
            'penguji_1_id' => $first->id, 'penguji_2_id' => $second->id,
            'tanggal' => '2026-10-01', 'jam_mulai' => '09:00', 'jam_selesai' => '10:00',
            'status' => 'terpublikasi', 'catatan' => 'Schedule note',
        ], $attributes));

        return [$exam, $first, $second];
    }

    public function test_completion_requires_both_scores_and_duplicates_never_overwrite(): void
    {
        [$exam, $first, $second] = $this->exam();
        $url = '/dosen/penilaian/'.$exam->id;
        $this->actingAs($first)->post($url, ['nilai' => 80])->assertSessionHas('success');
        $this->post($url, ['nilai' => 99])->assertSessionHasErrors('nilai');
        $this->actingAs($second)->post($url, ['nilai' => 90])->assertSessionHas('success');
        $this->assertSame('selesai', $exam->fresh()->status);
        $this->assertDatabaseCount('penilaian', 2);
        $this->post($url, ['nilai' => 70])->assertSessionHasErrors('nilai');
        $this->get('/dosen/penilaian')->assertInertia(fn (Assert $page) => $page->where('dbJadwalUji.0.nilai_akhir', 85)->where('dbJadwalUji.0.jumlah_penilaian', 2));
    }

    public function test_draft_cancelled_and_incomplete_panels_cannot_be_scored(): void
    {
        foreach (['draft', 'dibatalkan', 'selesai'] as $status) {
            [$exam, $first] = $this->exam(['status' => $status]);
            $this->actingAs($first)->post('/dosen/penilaian/'.$exam->id, ['nilai' => 80])->assertSessionHasErrors('nilai');
        }
        [$exam, $first] = $this->exam(['penguji_2_id' => null]);
        $this->actingAs($first)->post('/dosen/penilaian/'.$exam->id, ['nilai' => 80])->assertSessionHasErrors('nilai');
        $this->assertDatabaseCount('penilaian', 0);
    }

    public function test_scores_reject_excess_precision_and_schema_enforces_range_and_unique_examiner(): void
    {
        [$exam, $first] = $this->exam();
        $this->actingAs($first)->post('/dosen/penilaian/'.$exam->id, ['nilai' => 81.255])->assertSessionHasErrors('nilai');
        $this->assertDatabaseCount('penilaian', 0);
        $this->expectException(QueryException::class);
        DB::table('penilaian')->insert(['penjadwalan_id' => $exam->id, 'dosen_id' => $first->id, 'nilai' => 101]);
    }

    public function test_database_uniqueness_prevents_a_second_score_for_same_examiner(): void
    {
        [$exam, $first] = $this->exam();
        $values = ['penjadwalan_id' => $exam->id, 'dosen_id' => $first->id, 'nilai' => 80];
        DB::table('penilaian')->insert($values);
        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('penilaian')->insert($values);
    }

    public function test_completion_updates_only_matching_registration_and_exams_have_separate_scores(): void
    {
        [$exam, $first, $second] = $this->exam();
        $registration = PendaftaranSempro::create(['mahasiswa_id' => $exam->mahasiswa_id, 'judul_ta' => 'Actual', 'bentuk_ta' => 'Skripsi', 'status' => 'terjadwal']);
        $exam->update(['pendaftaran_id' => $registration->id]);
        $this->actingAs($first)->post('/dosen/penilaian/'.$exam->id, ['nilai' => 0])->assertSessionHas('success');
        $this->actingAs($second)->post('/dosen/penilaian/'.$exam->id, ['nilai' => 100])->assertSessionHas('success');
        $this->assertSame('selesai', $registration->fresh()->status);
        $another = $exam->replicate();
        $another->status = 'terpublikasi';
        $another->save();
        $this->actingAs($first)->post('/dosen/penilaian/'.$another->id, ['nilai' => 75])->assertSessionHas('success');
        $this->assertDatabaseCount('penilaian', 3);
    }

    public function test_score_and_examiner_note_survive_reload_without_completing_after_first_score(): void
    {
        [$exam, $first] = $this->exam();
        $this->actingAs($first)->post('/dosen/penilaian/'.$exam->id, ['nilai' => 81.25, 'catatan' => 'Examiner note'])->assertSessionHas('success');
        $this->assertDatabaseHas('penilaian', ['penjadwalan_id' => $exam->id, 'dosen_id' => $first->id, 'nilai' => 81.25, 'catatan' => 'Examiner note']);
        $this->assertSame('terpublikasi', $exam->fresh()->status);
        $this->assertSame('Schedule note', $exam->fresh()->catatan);
        $this->get('/dosen/penilaian')->assertInertia(fn (Assert $page) => $page
            ->where('dbJadwalUji.0.penilaian_saya.nilai', 81.25)
            ->where('dbJadwalUji.0.penilaian_saya.catatan', 'Examiner note')
            ->where('dbJadwalUji.0.jumlah_penilaian', 1)
            ->where('dbJadwalUji.0.nilai_akhir', null)
            ->where('dbJadwalUji.0.dapat_menilai', false));
    }
}
