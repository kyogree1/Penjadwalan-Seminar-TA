<?php

namespace Tests\Feature;

use App\Models\PengajuanJudul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentManagementFollowupTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_rejects_invalid_files_without_persisting_and_import_revalidates(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'tendik']));
        foreach (["name,email\n", "name,email\nGood,good@example.test\nBad,not-email\n", "name,email\nOne,dup@example.test\nTwo,DUP@example.test\n", "name,email\n\"Unclosed,x@example.test\n", "name,email\nOne,a@example.test,extra\n"] as $csv) {
            $file = UploadedFile::fake()->createWithContent('students.csv', $csv);
            $this->postJson('/tendik/mahasiswa/import-preview', ['file' => $file])->assertUnprocessable()->assertJsonValidationErrors('file');
            $this->post('/tendik/mahasiswa/import', ['file' => $file])->assertSessionHasErrors('file');
            $this->assertDatabaseCount('users', 1);
        }
        $file = UploadedFile::fake()->createWithContent('students.csv', "name,email\nOne,new@example.test\nTwo,race@example.test\n");
        $this->postJson('/tendik/mahasiswa/import-preview', ['file' => $file])->assertOk();
        User::factory()->create(['role' => 'dosen', 'email' => 'race@example.test']);
        $this->post('/tendik/mahasiswa/import', ['file' => $file])->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('users', ['email' => 'new@example.test']);
    }

    public function test_crud_preserves_identifiers_and_blocks_academic_history_deletion(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'tendik']));
        $this->post('/tendik/mahasiswa', ['name' => 'Created', 'email' => 'created@example.test'])->assertSessionHas('success');
        $student = User::where('email', 'created@example.test')->firstOrFail();
        $this->assertNull($student->username);
        $this->assertNull($student->nim_nip);
        $this->assertFalse(Hash::check('created@example.test', $student->password));
        $this->put('/tendik/mahasiswa/'.$student->id, ['name' => 'Changed', 'email' => 'changed@example.test'])->assertSessionHas('success');
        $this->assertSame('Changed', $student->fresh()->name);
        $this->put('/tendik/mahasiswa/'.$student->id, ['name' => 'Bad', 'email' => 'changed@example.test', 'username' => 'invented'])->assertSessionHasErrors('username');
        PengajuanJudul::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Actual title', 'bidang_penelitian' => 'AI', 'status' => 'menunggu']);
        $this->delete('/tendik/mahasiswa/'.$student->id)->assertSessionHasErrors('student');
        $this->assertDatabaseHas('users', ['id' => $student->id]);
        $empty = User::factory()->create(['role' => 'mahasiswa']);
        $this->delete('/tendik/mahasiswa/'.$empty->id)->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $empty->id]);
    }

    public function test_csv_routes_enforce_actor_roles_and_upload_bounds(): void
    {
        $this->get('/tendik/mahasiswa/template')->assertRedirect('/login');
        $this->postJson('/tendik/mahasiswa/import-preview')->assertUnauthorized();
        foreach (['mahasiswa', 'dosen', 'kaprodi', 'koordinator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get('/tendik/mahasiswa/template')->assertRedirect()->assertSessionHas('error');
            $this->post('/tendik/mahasiswa/import-preview')->assertRedirect()->assertSessionHas('error');
        }
        $this->actingAs(User::factory()->create(['role' => 'tendik']));
        $this->postJson('/tendik/mahasiswa/import-preview', ['file' => UploadedFile::fake()->create('large.csv', 1025)])->assertUnprocessable()->assertJsonValidationErrors('file');
        $csv = "name,email\n";
        for ($i = 0; $i < 501; $i++) {
            $csv .= "Student {$i},student{$i}@example.test\n";
        }
        $this->postJson('/tendik/mahasiswa/import-preview', ['file' => UploadedFile::fake()->createWithContent('many.csv', $csv)])->assertUnprocessable()->assertJsonValidationErrors('file');
    }

    public function test_preview_and_template_are_real_create_only_contracts(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'tendik']));
        $this->get('/tendik/mahasiswa/template')->assertOk()->assertDownload('template-mahasiswa.csv')->assertStreamedContent("name,email\n");
        $file = UploadedFile::fake()->createWithContent('students.csv', "name,email\nNew Student,new@example.test\n");
        $this->postJson('/tendik/mahasiswa/import-preview', ['file' => $file])->assertOk()->assertExactJson(['rows' => [['name' => 'New Student', 'email' => 'new@example.test']], 'count' => 1]);
        $this->assertDatabaseMissing('users', ['email' => 'new@example.test']);
        $this->post('/tendik/mahasiswa/import', ['file' => $file])->assertRedirect()->assertSessionHas('success');
        $this->postJson('/tendik/mahasiswa/import-preview', ['file' => $file])->assertUnprocessable()->assertJsonValidationErrors('file');
    }
}
