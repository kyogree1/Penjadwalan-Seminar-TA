<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TendikMahasiswaTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_exposes_only_persisted_student_fields_and_empty_results(): void
    {
        $actor = User::factory()->create(['role' => 'tendik']);
        $this->actingAs($actor)->get('/tendik/mahasiswa')->assertInertia(fn (Assert $page) => $page
            ->component('Tendik/Mahasiswa')->where('dbStudents', []));
        $student = User::factory()->create(['role' => 'mahasiswa', 'username' => null, 'nim_nip' => null]);
        $this->get('/tendik/mahasiswa')->assertInertia(fn (Assert $page) => $page
            ->has('dbStudents', 1)->where('dbStudents.0.id', $student->id)
            ->where('dbStudents.0.name', $student->name)->where('dbStudents.0.email', $student->email)
            ->where('dbStudents.0.username', null)->where('dbStudents.0.nim_nip', null)
            ->where('dbStudents.0.prodi', 'S1 Informatika')->has('dbStudents.0.created_at')
            ->missing('dbStudents.0.password')->missing('dbStudents.0.angkatan')
            ->missing('dbStudents.0.tahap')->missing('dbStudents.0.statusAkun'));
    }

    public function test_directory_and_reset_enforce_actor_and_target_roles(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa', 'username' => 'student-login']);
        $original = $student->password;
        $this->get('/tendik/mahasiswa')->assertRedirect('/login');
        $this->post('/tendik/mahasiswa/'.$student->id.'/reset-password')->assertRedirect('/login');
        foreach (['mahasiswa', 'dosen', 'kaprodi', 'koordinator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get('/tendik/mahasiswa')->assertRedirect()->assertSessionHas('error');
            $this->post('/tendik/mahasiswa/'.$student->id.'/reset-password')->assertRedirect()->assertSessionHas('error');
        }
        $this->assertSame($original, $student->fresh()->password);
        $target = User::factory()->create(['role' => 'dosen']);
        $targetPassword = $target->password;
        $this->actingAs(User::factory()->create(['role' => 'tendik']))
            ->post('/tendik/mahasiswa/'.$target->id.'/reset-password')->assertNotFound();
        $this->post('/tendik/mahasiswa/999999/reset-password')->assertNotFound();
        $this->assertSame($targetPassword, $target->fresh()->password);
    }

    public function test_missing_reset_identifier_fails_validation_without_changing_password(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'tendik']));
        foreach ([null, '', '   '] as $username) {
            $student = User::factory()->create(['role' => 'mahasiswa', 'username' => $username, 'nim_nip' => 'not-a-fallback']);
            $original = $student->password;
            $this->from('/tendik/mahasiswa')->post('/tendik/mahasiswa/'.$student->id.'/reset-password')
                ->assertRedirect('/tendik/mahasiswa')->assertSessionHasErrors('username')->assertSessionMissing('success');
            $this->assertSame($original, $student->fresh()->password);
        }
    }

    public function test_reset_uses_existing_username_not_nim_and_never_flashes_the_default(): void
    {
        $actor = User::factory()->create(['role' => 'tendik']);
        $student = User::factory()->create(['role' => 'mahasiswa', 'username' => 'existing-login', 'nim_nip' => 'different-nim']);
        $this->actingAs($actor)->from('/tendik/mahasiswa')->post('/tendik/mahasiswa/'.$student->id.'/reset-password')
            ->assertRedirect('/tendik/mahasiswa')->assertSessionHas('success', 'Password akun mahasiswa berhasil direset.');
        $this->assertTrue(Hash::check('existing-login', $student->fresh()->password));
        $this->assertFalse(Hash::check('different-nim', $student->fresh()->password));
    }
}
