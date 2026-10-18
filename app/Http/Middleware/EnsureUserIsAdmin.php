<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi area admin hanya untuk pengguna dengan peran admin.
 */
class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->isAdmin()) {
            return $next($request);
        }

        Inertia::flash('toast', [
            'type' => 'error',
            'message' => 'Halaman itu hanya bisa dibuka admin.',
        ]);

        return to_route('dashboard');
    }
}
