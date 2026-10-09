<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Display the login view or redirect if already authenticated.
     */
    public function showLogin(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return Inertia::render('Auth/Login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request)
    {
        // Passwordless demo login is only available on explicitly local servers.
        if ($request->filled('demo_role')) {
            abort_unless(app()->environment('local'), 403);

            $demoRole = $request->input('demo_role');
            $user = User::where('role', $demoRole)->first();

            if ($user) {
                Auth::login($user, (bool) $request->input('remember', true));
                $request->session()->regenerate();

                return $this->redirectBasedOnRole($user);
            }
        }

        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = $credentials['email'];
        $password = $credentials['password'];
        $remember = (bool) $request->input('remember', false);

        // Allow logging in via email or username (NIM / NIP)
        $user = User::where('email', $loginInput)
            ->orWhere('username', $loginInput)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user, $remember);
            $request->session()->regenerate();

            return $this->redirectBasedOnRole($user);
        }

        throw ValidationException::withMessages([
            'email' => 'Kombinasi Username/Email dan Password yang Anda masukkan tidak sesuai.',
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Redirect user to their dedicated portal based on their role.
     */
    protected function redirectBasedOnRole(User $user)
    {
        return match ($user->role) {
            'dosen' => redirect()->route('dosen.dashboard'),
            'kaprodi', 'koordinator' => redirect()->route('kaprodi.dashboard'),
            'tendik' => redirect()->route('tendik.dashboard'),
            default => redirect()->route('dashboard'),
        };
    }
}
