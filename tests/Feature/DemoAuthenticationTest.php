<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_role_login_is_rejected(): void
    {
        User::factory()->create(['role' => 'dosen']);
        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->post('/login', ['demo_role' => 'dosen'])->assertForbidden();

        $this->assertGuest();
    }

    public function test_password_login_still_works(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->post('/login', ['email' => $dosen->email, 'password' => 'password'])
            ->assertRedirect('/dosen/dashboard');

        $this->assertAuthenticatedAs($dosen);
    }
}
