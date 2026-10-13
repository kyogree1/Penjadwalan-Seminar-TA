<?php

namespace Tests\Feature;

use App\Models\Bimbingan;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\Penjadwalan;
use App\Models\SchedulePeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TrackHBoundaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_tendik_account_management_is_bounded_and_persistent(): void
    {
        $tendik = User::factory()->create(['role' => 'tendik']);
        $this->actingAs($tendik)->post('/tendik/mahasiswa', ['name' => 'New student', 'email' => 'new@example.test', 'role' => 'dosen'])->assertSessionHasErrors('role');
        $this->post('/tendik/mahasiswa', ['name' => 'New student', 'email' => 'new@example.test'])->assertSessionHas('success');
        $student = User::where('email', 'new@example.test')->firstOrFail();
        $this->assertSame('mahasiswa', $student->role);
        $this->assertNull($student->username);
        $this->assertFalse(Hash::check('password123', $student->password));
        $this->put('/tendik/mahasiswa/'.$student->id, ['name' => 'Changed', 'email' => 'changed@example.test'])->assertSessionHas('success');
        $this->get('/tendik/mahasiswa')->assertInertia(fn (Assert $page) => $page->component('Tendik/Mahasiswa')->where('dbStudents.0.name', 'Changed'));
        $this->put('/tendik/mahasiswa/'.$tendik->id, ['name' => 'Forbidden', 'email' => 'other@example.test'])->assertNotFound();
        $this->put('/tendik/mahasiswa/'.$student->id, ['name' => 'Bad', 'email' => $tendik->email])->assertSessionHasErrors('email');
        $this->assertSame('Changed', $student->fresh()->name);
        $this->actingAs($student)->post('/tendik/mahasiswa', ['name' => 'Denied', 'email' => 'denied@example.test'])->assertRedirect();
        $this->assertDatabaseMissing('users', ['email' => 'denied@example.test']);
        $this->actingAs($tendik)->delete('/tendik/mahasiswa/'.$student->id)->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $student->id]);
    }

    public function test_student_csv_import_is_create_only_and_atomic(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'tendik']));
        $file = fn ($text) => UploadedFile::fake()->createWithContent('students.csv', $text);
        $this->post('/tendik/mahasiswa/import', ['file' => $file("name,email\nCSV student,csv@example.test\n")])->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'csv@example.test', 'role' => 'mahasiswa']);
        $this->post('/tendik/mahasiswa/import', ['file' => $file("name,email\nMust not persist,new2@example.test\nDuplicate,csv@example.test\n")])->assertSessionHasErrors();
        $this->assertDatabaseMissing('users', ['email' => 'new2@example.test']);
        $this->post('/tendik/mahasiswa/import', ['file' => $file("name,email\nOne,repeated@example.test\nTwo,repeated@example.test\n")])->assertSessionHasErrors();
        $this->assertDatabaseMissing('users', ['email' => 'repeated@example.test']);
        $this->post('/tendik/mahasiswa/import', ['file' => $file("name,email,cohort\nUnsupported,u@example.test,2026\n")])->assertSessionHasErrors('file');
        $this->post('/tendik/mahasiswa/import', ['file' => $file("name,email\nBad\"quote,bad@example.test\n")])->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('users', ['email' => 'bad@example.test']);
    }

    public function test_account_with_academic_history_cannot_be_deleted(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        PengajuanJudul::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Keep', 'bidang_penelitian' => 'AI']);
        $this->actingAs(User::factory()->create(['role' => 'tendik']))->delete('/tendik/mahasiswa/'.$student->id)->assertSessionHasErrors('student');
        $this->assertDatabaseHas('users', ['id' => $student->id]);
        $this->assertDatabaseHas('pengajuan_judul', ['mahasiswa_id' => $student->id]);
    }

    public function test_examiner_document_delivery_checks_assignment_type_owner_and_private_storage(): void
    {
        Storage::fake('local');
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $examiner = User::factory()->create(['role' => 'dosen']);
        $other = User::factory()->create(['role' => 'dosen']);
        $path = 'dokumen/sidang/'.$student->id.'/draft.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 real test document');
        $registration = PendaftaranSidang::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Thesis', 'draft_laporan_file' => $path]);
        $exam = Penjadwalan::create(['tipe' => 'sidang', 'pendaftaran_id' => $registration->id, 'mahasiswa_id' => $student->id, 'penguji_1_id' => $examiner->id, 'pembimbing_1_id' => $other->id, 'tanggal' => '2026-10-11', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:00:00', 'status' => 'terpublikasi']);
        $url = '/dosen/penilaian/'.$exam->id.'/dokumen/draft_laporan_file';
        $response = $this->actingAs($examiner)->get($url)->assertOk();
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertSame('%PDF-1.4 real test document', $response->streamedContent());
        $this->get('/dosen/penilaian')->assertInertia(fn (Assert $page) => $page->component('Dosen/Penilaian')->has('dbJadwalUji.0.documents', 1)->where('dbJadwalUji.0.documents.0.url', $url));
        $this->actingAs($other)->get($url)->assertNotFound();
        $this->actingAs($examiner)->get('/dosen/penilaian/'.$exam->id.'/dokumen/proposal_file')->assertNotFound();
        $exam->update(['status' => 'draft']);
        $this->get($url)->assertNotFound();
        $exam->update(['status' => 'terpublikasi', 'mahasiswa_id' => $other->id]);
        $this->get($url)->assertNotFound();
        $exam->update(['mahasiswa_id' => $student->id]);
        $registration->update(['draft_laporan_file' => '../outside.pdf']);
        $this->get($url)->assertNotFound();
        $registration->update(['draft_laporan_file' => $path]);
        Storage::disk('local')->delete($path);
        $this->get($url)->assertNotFound();
    }

    public function test_new_registration_documents_are_stored_privately(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $student = User::factory()->create(['role' => 'mahasiswa']);
        foreach (['sempro' => 'proposal_file', 'sidang' => 'draft_laporan_file'] as $type => $field) {
            SchedulePeriod::create(['name' => 'Upload wave', 'tipe' => $type, 'gelombang' => 1, 'capacity' => 10, 'start_date' => '2026-11-01', 'end_date' => '2026-11-02', 'slots' => [], 'is_open' => true]);
            $payload = ['judul_ta' => 'Private draft', 'bentuk_ta' => 'Skripsi', $field => UploadedFile::fake()->create('document.pdf', 10, 'application/pdf')];
            if ($type === 'sidang') {
                $payload['skor_iaet'] = 500;
            }
            $this->actingAs($student)->post('/pendaftaran/'.$type, $payload)->assertSessionHas('success');
            $model = $type === 'sempro' ? PendaftaranSempro::class : PendaftaranSidang::class;
            $path = $model::firstOrFail()->{$field};
            Storage::disk('local')->assertExists($path);
            Storage::disk('public')->assertMissing($path);
        }
    }

    public function test_completed_approved_defense_is_not_manuscript_publication(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        PendaftaranSidang::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Not a published manuscript', 'status' => 'selesai', 'status_akademik' => 'disetujui']);
        $this->assertFalse(Schema::hasColumn('pendaftaran_sidang', 'published_at'));
        $this->actingAs($student)->get('/katalog')->assertInertia(fn (Assert $page) => $page->component('Katalog/Index')->missing('publications'));
    }

    public function test_student_logbook_requires_approved_supervisor(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $other = User::factory()->create(['role' => 'dosen']);
        $data = ['tanggal' => '2026-10-11', 'dosen' => $lecturer->id, 'rangkuman' => 'Discuss thesis', 'keterangan' => 'Bab 1'];
        $this->actingAs($student)->post('/pendaftaran/bimbingan', $data)->assertSessionHasErrors('dosen');
        $this->assertDatabaseCount('bimbingan', 0);
        $title = PengajuanJudul::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Actual title', 'bidang_penelitian' => 'AI', 'status' => 'menunggu', 'pembimbing_1_id' => $lecturer->id]);
        $this->post('/pendaftaran/bimbingan', $data)->assertSessionHasErrors('dosen');
        $title->update(['status' => 'disetujui']);
        $this->get('/pendaftaran/bimbingan')->assertInertia(fn (Assert $page) => $page->component('Pendaftaran/Bimbingan')->has('dosenList', 1)->where('dosenList.0.id', $lecturer->id));
        $this->post('/pendaftaran/bimbingan', [...$data, 'dosen' => $other->id])->assertSessionHasErrors('dosen');
        $this->post('/pendaftaran/bimbingan', $data)->assertSessionHas('success');
        $this->assertSame('process', Bimbingan::firstOrFail()->status);
        $this->actingAs($lecturer)->get('/dosen/bimbingan')->assertOk();
    }
}
