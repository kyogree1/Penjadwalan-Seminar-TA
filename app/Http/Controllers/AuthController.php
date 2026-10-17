<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\Penjadwalan;
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

        $schedules = Penjadwalan::query()
            ->where('penjadwalan.status', 'terpublikasi')
            ->with(['mahasiswa:id,name', 'pembimbing1:id,name', 'pembimbing2:id,name', 'penguji1:id,name', 'penguji2:id,name'])
            ->orderBy('tanggal')->orderBy('jam_mulai')->orderBy('penjadwalan.id')
            ->get();
        $titles = [];
        foreach (['sempro' => PendaftaranSempro::class, 'sidang' => PendaftaranSidang::class] as $type => $model) {
            $titles[$type] = $model::whereIn('id', $schedules->where('tipe', $type)->pluck('pendaftaran_id'))
                ->get(['id', 'mahasiswa_id', 'judul_ta'])->keyBy('id');
        }

        $activePeriods = \Illuminate\Support\Facades\DB::table('schedule_periods')
            ->where('is_open', true)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'type' => $p->tipe,
                    'gelombang' => $p->gelombang,
                    'start_date' => $p->start_date,
                    'end_date' => $p->end_date,
                    'is_open' => (bool) $p->is_open,
                ];
            });

        return Inertia::render('Auth/Login', [
            'publicPeriods' => $activePeriods,
            'publicSchedules' => $schedules->map(function ($schedule) use ($titles) {
                $registration = $titles[$schedule->tipe]->get($schedule->pendaftaran_id);

                return [
                    'student_name' => $schedule->mahasiswa?->name,
                    'title' => $registration?->mahasiswa_id === $schedule->mahasiswa_id ? $registration->judul_ta : null,
                    'type' => $schedule->tipe,
                    'date' => $schedule->tanggal->format('Y-m-d'),
                    'start_time' => $schedule->jam_mulai,
                    'end_time' => $schedule->jam_selesai,
                    'mode' => $schedule->mode,
                    'supervisors' => array_values(array_filter([$schedule->pembimbing1?->name, $schedule->pembimbing2?->name])),
                    'examiners' => array_values(array_filter([$schedule->penguji1?->name, $schedule->penguji2?->name])),
                ];
            })->values(),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request)
    {
        abort_if($request->filled('demo_role'), 403);

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
