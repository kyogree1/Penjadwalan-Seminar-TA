<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Support 'kaprodi' and 'koordinator' interchangeably
        $userRole = $user->role;
        $normalizedRoles = array_map(function ($r) {
            return $r === 'koordinator' ? 'kaprodi' : $r;
        }, $roles);

        $checkUserRole = $userRole === 'koordinator' ? 'kaprodi' : $userRole;

        if (!empty($roles) && !in_array($checkUserRole, $normalizedRoles, true)) {
            // Redirect to the user's correct role dashboard
            return match ($user->role) {
                'dosen' => redirect()->route('dosen.dashboard')->with('error', 'Halaman tersebut hanya untuk peran tertentu.'),
                'kaprodi', 'koordinator' => redirect()->route('kaprodi.dashboard')->with('error', 'Halaman tersebut hanya untuk peran tertentu.'),
                'tendik' => redirect()->route('tendik.dashboard')->with('error', 'Halaman tersebut hanya untuk peran tertentu.'),
                default => redirect()->route('dashboard')->with('error', 'Halaman tersebut hanya untuk peran tertentu.'),
            };
        }

        return $next($request);
    }
}
