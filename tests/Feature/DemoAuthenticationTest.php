<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DemoAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('nonLocalEnvironments')]
    public function test_demo_login_is_forbidden_outside_local_environment(string $environment): void
    {
        User::factory()->create(['role' => 'dosen']);
        $this->app->instance('env', $environment);
        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->post('/login', ['demo_role' => 'dosen'])->assertForbidden();

        $this->assertGuest();
    }

    public static function nonLocalEnvironments(): array
    {
        return [['production'], ['staging'], ['testing']];
    }

    public function test_local_demo_login_still_authenticates_the_requested_role(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $this->app->instance('env', 'local');
        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->post('/login', ['demo_role' => 'dosen'])->assertRedirect('/dosen/dashboard');

        $this->assertAuthenticatedAs($dosen);
    }

    public function test_production_password_login_still_works(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $this->app->instance('env', 'production');
        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->post('/login', ['email' => $dosen->email, 'password' => 'password'])
            ->assertRedirect('/dosen/dashboard');

        $this->assertAuthenticatedAs($dosen);
    }
}
