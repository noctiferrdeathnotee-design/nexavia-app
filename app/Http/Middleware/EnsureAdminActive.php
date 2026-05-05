<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()?->status === 'nonaktif') {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Akun Anda dinonaktifkan.',
                ]);
        }

        return $next($request);
    }
}
