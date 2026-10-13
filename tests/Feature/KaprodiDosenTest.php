<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class KaprodiDosenTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_persists_supported_fields_and_validates_unique_identifiers(): void
    {
        $actor = User::factory()->create(['role' => 'kaprodi']);
        $payload = ['name' => 'Dosen Baru', 'email' => 'baru@example.test', 'nip' => '12345', 'prodi' => 'S1 Informatika', 'jabatan' => 'Lektor'];
        $this->actingAs($actor)->from('/kaprodi/dosen')->post('/kaprodi/dosen', $payload + ['role' => 'kaprodi', 'maxQuota' => 99])
            ->assertRedirect('/kaprodi/dosen')->assertSessionHas('success');
        $dosen = User::where('email', $payload['email'])->firstOrFail();
        $this->assertSame('dosen', $dosen->role);
        $this->assertSame('12345', $dosen->username);
        $this->assertDatabaseHas('users', ['id' => $dosen->id, 'nim_nip' => '12345', 'jabatan' => 'Lektor']);
        $this->post('/kaprodi/dosen', array_replace($payload, ['email' => 'other@example.test']))->assertSessionHasErrors('nip');
        $this->post('/kaprodi/dosen', array_replace($payload, ['nip' => '67890']))->assertSessionHasErrors('email');
        $this->post('/kaprodi/dosen', [])->assertSessionHasErrors(['name', 'email', 'nip', 'prodi']);
        $updated = array_replace($payload, ['name' => 'Dosen Diperbarui', 'nip' => '54321', 'jabatan' => null]);
        $this->put('/kaprodi/dosen/'.$dosen->id, $updated)->assertRedirect('/kaprodi/dosen')->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['id' => $dosen->id, 'name' => $updated['name'], 'username' => '54321', 'nim_nip' => '54321', 'jabatan' => null]);
        $this->put('/kaprodi/dosen/'.$dosen->id, array_replace($updated, ['email' => $actor->email, 'nip' => null]))->assertSessionHasErrors(['email', 'nip']);
        $this->delete('/kaprodi/dosen/'.$dosen->id)->assertRedirect('/kaprodi/dosen')->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $dosen->id]);
    }

    public function test_optional_jabatan_is_not_fabricated_on_creation(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kaprodi']))
            ->post('/kaprodi/dosen', ['name' => 'Tanpa Jabatan', 'email' => 'null@example.test', 'nip' => '009', 'prodi' => 'S1 Informatika'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'null@example.test', 'jabatan' => null]);
    }

    public function test_directory_and_mutations_are_role_scoped(): void
    {
        $target = User::factory()->create(['role' => 'mahasiswa']);
        $this->get('/kaprodi/dosen')->assertRedirect('/login');
        foreach (['mahasiswa', 'dosen', 'tendik'] as $role) {
            $actor = User::factory()->create(['role' => $role]);
            $this->actingAs($actor)->get('/kaprodi/dosen')->assertRedirect()->assertSessionHas('error');
            $this->post('/kaprodi/dosen', [])->assertRedirect()->assertSessionHas('error');
            $this->put('/kaprodi/dosen/'.$target->id, [])->assertRedirect()->assertSessionHas('error');
            $this->delete('/kaprodi/dosen/'.$target->id)->assertRedirect()->assertSessionHas('error');
        }
        $actor = User::factory()->create(['role' => 'koordinator']);
        $this->actingAs($actor)->put('/kaprodi/dosen/'.$target->id, [])->assertNotFound();
        $this->delete('/kaprodi/dosen/'.$target->id)->assertNotFound();
        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function test_directory_serializes_only_persisted_lecturer_fields_and_empty_results(): void
    {
        $actor = User::factory()->create(['role' => 'koordinator']);
        $this->actingAs($actor)->get('/kaprodi/dosen')->assertInertia(fn (Assert $page) => $page
            ->component('Kaprodi/Dosen')->where('dosenList', [])->missing('dbDosenList'));
        $dosen = User::factory()->create(['role' => 'dosen', 'nim_nip' => null, 'jabatan' => null]);
        $this->get('/kaprodi/dosen')->assertInertia(fn (Assert $page) => $page->where('dosenList', [[
            'id' => $dosen->id, 'name' => $dosen->name, 'email' => $dosen->email,
            'nip' => null, 'prodi' => 'S1 Informatika', 'jabatan' => null,
        ]]));
    }
}
