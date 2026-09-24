<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RoleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }
    public function test_guest_is_served_login_page_on_root_route(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_mahasiswa_can_login_and_is_redirected_to_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => '11231006',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_dosen_can_login_and_is_redirected_to_dosen_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => '198504122010121003',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dosen/dashboard');
        $this->assertAuthenticated();
    }

    public function test_kaprodi_can_login_and_is_redirected_to_kaprodi_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => '198003152005011002',
            'password' => 'password',
        ]);

        $response->assertRedirect('/kaprodi/dashboard');
        $this->assertAuthenticated();
    }

    public function test_tendik_can_login_and_is_redirected_to_tendik_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => '199208192018032001',
            'password' => 'password',
        ]);

        $response->assertRedirect('/tendik/dashboard');
        $this->assertAuthenticated();
    }

    public function test_mahasiswa_cannot_access_dosen_or_tendik_portal(): void
    {
        $mahasiswa = User::where('role', 'mahasiswa')->first();

        $response = $this->actingAs($mahasiswa)->get('/dosen/dashboard');
        $response->assertRedirect('/dashboard');

        $response2 = $this->actingAs($mahasiswa)->get('/tendik/dashboard');
        $response2->assertRedirect('/dashboard');
    }

    public function test_dosen_cannot_access_tendik_or_mahasiswa_portal(): void
    {
        $dosen = User::where('role', 'dosen')->first();

        $response = $this->actingAs($dosen)->get('/dashboard');
        $response->assertRedirect('/dosen/dashboard');

        $response2 = $this->actingAs($dosen)->get('/tendik/dashboard');
        $response2->assertRedirect('/dosen/dashboard');
    }

    public function test_logout_invalidates_session_and_redirects_to_login(): void
    {
        $mahasiswa = User::where('role', 'mahasiswa')->first();

        $response = $this->actingAs($mahasiswa)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
