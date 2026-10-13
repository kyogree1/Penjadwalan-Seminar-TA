<?php

namespace Tests\Feature;

use App\Models\Penjadwalan;
use App\Models\SchedulePeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class R5OutputsTest extends TestCase
{
    use RefreshDatabase;

    private function csv(string $text): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('lecturers.csv', $text);
    }

    public function test_preview_commit_and_reload_use_real_csv_and_never_update_duplicates(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kaprodi']));
        $csv = "name,email,nip,prodi,jabatan\r\n\"Dosen, Aktual\",actual@example.test,00012,Informatika,Lektor\r\n";
        $this->postJson('/kaprodi/dosen/import/preview', ['file' => $this->csv($csv)])
            ->assertOk()->assertJsonPath('valid', true)->assertJsonPath('rows.0.data.nip', '00012');
        $this->assertDatabaseCount('users', 1);
        $this->post('/kaprodi/dosen/import', ['file' => $this->csv($csv)])->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['name' => 'Dosen, Aktual', 'username' => '00012', 'nim_nip' => '00012', 'role' => 'dosen']);
        $this->get('/kaprodi/dosen')->assertInertia(fn (Assert $page) => $page->where('dosenList.0.nip', '00012'));
        $this->postJson('/kaprodi/dosen/import/preview', ['file' => $this->csv($csv)])
            ->assertOk()->assertJsonPath('valid', false)->assertJsonStructure(['rows' => [['errors' => ['nip', 'email']]]]);
        $this->post('/kaprodi/dosen/import', ['file' => $this->csv($csv)])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('users', 2);
    }

    public function test_malformed_and_duplicate_rows_are_actionable_and_atomic(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'koordinator']));
        foreach (["wrong,header\n1,2", "name,email,nip,prodi,jabatan\n\"unclosed", "name,email,nip,prodi,jabatan\nA,a@example.test,123,IF,\nB,a@example.test,123,IF,", "name,email,nip,prodi,jabatan\nA,invalid,123,,"] as $csv) {
            $this->postJson('/kaprodi/dosen/import/preview', ['file' => $this->csv($csv)])
                ->assertOk()->assertJsonPath('valid', false);
            $this->post('/kaprodi/dosen/import', ['file' => $this->csv($csv)])->assertSessionHasErrors('file');
            $this->assertDatabaseCount('users', 1);
        }
    }

    public function test_export_contains_persisted_rows_period_scope_and_escaped_formula_cells(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'koordinator']));
        $student = User::factory()->create(['role' => 'mahasiswa', 'name' => '=FORMULA', 'nim_nip' => '00123']);
        $period = SchedulePeriod::create(['name' => 'Actual period', 'tipe' => 'sempro', 'gelombang' => 1, 'capacity' => 10, 'start_date' => '2026-11-01', 'end_date' => '2026-11-02', 'slots' => [], 'is_open' => false]);
        Penjadwalan::create(['period_id' => $period->id, 'tipe' => 'sempro', 'pendaftaran_id' => 42, 'mahasiswa_id' => $student->id, 'tanggal' => '2026-11-01', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00', 'status' => 'terpublikasi']);
        $result = $this->get('/kaprodi/penjadwalan/export?period_id='.$period->id)->assertOk()->assertDownload('jadwal.csv');
        $content = $result->streamedContent();
        $lines = explode("\r\n", trim($content));
        $this->assertCount(2, $lines);
        $cells = str_getcsv($lines[1], ',', '"', '');
        $this->assertSame("'=FORMULA", $cells[5]);
        $this->assertSame('00123', $cells[6]);
        $this->assertSame('2026-11-01', $cells[7]);
        $this->assertSame('terpublikasi', $cells[17]);
        $other = $period->replicate();
        $other->save();
        $empty = $this->get('/kaprodi/penjadwalan/export?period_id='.$other->id)->assertOk()->streamedContent();
        $this->assertStringNotContainsString('FORMULA', $empty);
    }

    public function test_template_and_empty_schedule_export_are_csv_downloads_with_real_contents(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kaprodi']));
        $template = $this->get('/kaprodi/dosen/template')->assertOk()->assertDownload('template-dosen.csv');
        $this->assertSame("name,email,nip,prodi,jabatan\r\n", $template->streamedContent());
        $export = $this->get('/kaprodi/penjadwalan/export')->assertOk()->assertDownload('jadwal.csv');
        $this->assertStringContainsString('mahasiswa', $export->streamedContent());
        $this->get('/kaprodi/penjadwalan/export?period_id=99999')->assertNotFound();
        $this->actingAs(User::factory()->create(['role' => 'mahasiswa']));
        $this->post('/kaprodi/dosen/import', ['file' => $this->csv('bad')])->assertRedirect()->assertSessionHas('error');
        $this->get('/kaprodi/dosen/template')->assertRedirect()->assertSessionHas('error');
        $this->get('/kaprodi/penjadwalan/export')->assertRedirect()->assertSessionHas('error');
    }
}
