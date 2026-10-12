<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AccountPasswordReset;
use App\Services\AtomicPasswordReset;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PasswordResetController extends Controller
{
    public function request()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function send(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email', 'max:255']]);
        // Never write reset links into a development log mailer.
        $mailer = config('mail.default');
        if (in_array($mailer, ['smtp', 'ses', 'ses-v2', 'postmark', 'resend', 'mailgun'], true)
            || (app()->environment('testing') && $mailer === 'array')) {
            try {
                Password::sendResetLink($credentials, function ($user, $token) {
                    $config = config('auth.passwords.'.config('auth.defaults.passwords'));
                    $tokens = DB::connection($config['connection'] ?? null)->table($config['table']);
                    $row = (clone $tokens)->where('email', $user->getEmailForPasswordReset())->first();
                    try {
                        $user->notify(new AccountPasswordReset($token));
                    } catch (\Throwable) {
                        // Never delete a replacement issued by another request, or report
                        // transport exception text (it may contain addresses/reset URLs).
                        if ($row && Hash::check($token, $row->token)) {
                            (clone $tokens)->where('email', $row->email)->where('token', $row->token)
                                ->where('created_at', $row->created_at)->delete();
                        }
                    }
                });
            } catch (\Throwable) {
                // Infrastructure failures must not change the public account lookup response.
            }
        }

        return back()->with('success', 'Jika email terdaftar, tautan pengaturan password akan dikirim. Periksa kotak masuk Anda.');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->only('email', 'token', 'password', 'password_confirmation'), [
            'email' => ['required', 'email', 'max:255'],
            'token' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255', 'confirmed', \Illuminate\Validation\Rules\Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $status = app(AtomicPasswordReset::class)->reset($validator->validated(), function (User $user, string $password) {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'Tautan tidak valid atau kedaluwarsa. Minta tautan baru.']);
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Password berhasil diatur. Silakan masuk.');
    }

    public function edit(Request $request, string $token)
    {
        return Inertia::render('Auth/ResetPassword', ['token' => $token, 'email' => (string) $request->query('email', '')])
            ->toResponse($request)->withHeaders(['Referrer-Policy' => 'no-referrer', 'Cache-Control' => 'no-store, private']);
    }
}
