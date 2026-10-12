<?php

namespace Tests\Feature;

use App\Http\Controllers\PasswordResetController;
use App\Models\User;
use App\Notifications\AccountPasswordReset as ResetPassword;
use App\Services\AtomicPasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AccountAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_failure_is_generic_cleans_undelivered_token_and_allows_retry(): void
    {
        config(['mail.default' => 'smtp']);
        $user = User::factory()->create();
        $fail = true;
        Event::listen(NotificationSending::class, function () use (&$fail) {
            if ($fail) {
                throw new \RuntimeException('Private transport details must not be reported');
            }
        });
        $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect('/forgot-password')->assertSessionHas('success');
        $message = session('success');
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        $this->from('/forgot-password')->post('/forgot-password', ['email' => 'absent@example.test'])
            ->assertRedirect('/forgot-password')->assertSessionHas('success', $message);
        $fail = false;
        Notification::fake();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('success', $message);
        Notification::assertSentToTimes($user, ResetPassword::class, 1);
        $token = Notification::sent($user, ResetPassword::class)->first()->token;
        $this->assertTrue(Password::tokenExists($user, $token));
    }

    public function test_failed_notification_cleanup_preserves_a_concurrently_replaced_token(): void
    {
        config(['mail.default' => 'smtp']);
        $user = User::factory()->create();
        $replacement = null;
        Event::listen(NotificationSending::class, function () use ($user, &$replacement) {
            $replacement = Password::createToken($user);
            throw new \RuntimeException('Isolated failure before transport');
        });
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('success');
        $this->assertTrue(Password::tokenExists($user, $replacement));
    }

    public function test_failed_password_write_rolls_back_the_single_use_claim(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $original = $user->password;
        try {
            app(AtomicPasswordReset::class)->reset([
                'email' => $user->email, 'token' => $token, 'password' => 'Strong!Password902',
            ], function ($account, $password) {
                $account->forceFill(['password' => $password])->save();
                throw new \RuntimeException('Isolated write failure');
            });
            $this->fail('Write failure was not propagated');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Isolated write failure', $exception->getMessage());
        }
        $this->assertSame($original, $user->fresh()->password);
        $this->assertTrue(Password::tokenExists($user, $token));
    }

    public function test_concurrent_validations_allow_only_one_password_reset(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $reads = 0;
        DB::listen(function ($query) use (&$reads) {
            if (\Fiber::getCurrent() && str_starts_with(strtolower($query->sql), 'select') && str_contains($query->sql, 'password_reset_tokens') && ++$reads === 2) {
                \Fiber::suspend();
            }
        });
        $reset = function ($password) use ($user, $token) {
            $request = Request::create('/reset-password', 'POST', [
                'email' => $user->email, 'token' => $token,
                'password' => $password, 'password_confirmation' => $password,
            ]);
            $request->setLaravelSession(app('session.store'));

            return app(PasswordResetController::class)->update($request);
        };
        $first = new \Fiber(fn () => $reset('First!Password902'));
        $first->start();
        $this->assertTrue($first->isSuspended());
        $second = $reset('Second!Password902');
        $first->resume();
        $this->assertStringEndsWith('/login', $second->getTargetUrl());
        $this->assertStringNotContainsString('/login', $first->getReturn()->getTargetUrl());
        $this->assertTrue(Hash::check('Second!Password902', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_mail_possession_completes_first_access_consumes_token_and_revokes_sessions(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email_verified_at' => null, 'remember_token' => 'old-remember']);
        DB::table('sessions')->insert([
            'id' => 'old-session', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time(),
        ]);
        $this->post('/forgot-password', ['email' => $user->email]);
        $token = Notification::sent($user, ResetPassword::class)->first()->token;
        $password = 'New-First-Access!902';
        $payload = ['email' => $user->email, 'token' => $token, 'password' => $password, 'password_confirmation' => $password];
        $this->post('/reset-password', $payload)->assertRedirect('/login')->assertSessionHas('success');
        $this->assertGuest();
        $user->refresh();
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertNotSame('old-remember', $user->remember_token);
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        $this->post('/reset-password', $payload)->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $user->email, 'password' => $password])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_reset_mail_uses_trusted_application_origin_and_never_log_transport(): void
    {
        Notification::fake();
        config(['app.url' => 'https://accounts.example.test', 'mail.default' => 'log']);
        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('success');
        Notification::assertNothingSent();
        config(['mail.default' => 'array']);
        $this->withHeader('Host', 'attacker.example.test')->post('/forgot-password', ['email' => $user->email]);
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $this->assertStringStartsWith('https://accounts.example.test/reset-password/', $notification->toMail($user)->actionUrl);

            return true;
        });
    }

    public function test_expired_invalid_and_wrong_email_tokens_cannot_change_password(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $original = $user->password;
        $this->post('/forgot-password', ['email' => $user->email]);
        $token = Notification::sent($user, ResetPassword::class)->first()->token;
        foreach ([['email' => $user->email, 'token' => 'invalid'], ['email' => 'unknown@example.test', 'token' => $token]] as $identity) {
            $this->post('/reset-password', $identity + ['password' => 'Strong!Password902', 'password_confirmation' => 'Strong!Password902'])->assertSessionHasErrors('email');
        }
        $this->travel(61)->minutes();
        $this->post('/reset-password', ['email' => $user->email, 'token' => $token, 'password' => 'Strong!Password902', 'password_confirmation' => 'Strong!Password902'])->assertSessionHasErrors('email');
        $this->assertSame($original, $user->fresh()->password);
    }

    public function test_confirmation_strength_and_validation_never_flash_secrets(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email]);
        $token = Notification::sent($user, ResetPassword::class)->first()->token;
        foreach ([['weak', 'weak'], ['Strong!Password902', 'different']] as [$password, $confirmation]) {
            $this->post('/reset-password', ['email' => $user->email, 'token' => $token, 'password' => $password, 'password_confirmation' => $confirmation])->assertSessionHasErrors('password');
            $this->assertNull(session('_old_input.token'));
            $this->assertNull(session('_old_input.password'));
            $this->assertNull(session('_old_input.password_confirmation'));
        }
        $this->assertTrue(Password::tokenExists($user, $token));
    }

    public function test_issuance_is_throttled_and_authenticated_users_cannot_issue(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('success');
        }
        Notification::assertSentToTimes($user, ResetPassword::class, 1);
        $this->post('/forgot-password', ['email' => 'absent@example.test'])->assertStatus(429);
        $this->actingAs($user)->post('/forgot-password', ['email' => $user->email])->assertRedirect();
        Notification::assertSentToTimes($user, ResetPassword::class, 1);
    }

    public function test_guest_can_request_a_private_non_enumerating_first_access_link(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email_verified_at' => null]);
        $known = $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email]);
        $known->assertRedirect('/forgot-password')->assertSessionHas('success');
        $message = session('success');
        $this->from('/forgot-password')->post('/forgot-password', ['email' => 'absent@example.test'])
            ->assertRedirect('/forgot-password')->assertSessionHas('success', $message);
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $url = $notification->toMail($user)->actionUrl;
            $this->assertStringContainsString('/reset-password/', $url);
            $this->get($url)->assertOk()->assertHeader('Referrer-Policy', 'no-referrer')->assertHeader('Cache-Control', 'no-store, private');
            $this->assertNotSame($notification->token, session('success'));

            return true;
        });
        $this->assertGuest();
    }
}
