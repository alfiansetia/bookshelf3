<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menghentikan akses pengguna yang diblokir.
 *
 * Pemeriksaan utama dilakukan saat login, tetapi sesi yang masih hidup ketika
 * akun diblokir juga harus langsung diputus.
 */
class EnsureUserIsNotBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isBlocked()) {
            return $next($request);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Inertia::flash('toast', [
            'type' => 'error',
            'message' => 'Akun kamu diblokir: '.$user->blockedMessage(),
        ]);

        return to_route('login');
    }
}
