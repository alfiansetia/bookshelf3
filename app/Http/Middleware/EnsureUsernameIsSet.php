<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memastikan pengguna (misal pendaftar lewat Google) sudah punya username rak.
 */
class EnsureUsernameIsSet
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->hasUsername()) {
            return to_route('onboarding.username.edit');
        }

        return $next($request);
    }
}
