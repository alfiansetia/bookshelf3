<?php

use App\Http\Middleware\EnsureUsernameIsSet;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // TLS diakhiri oleh Cloudflare Tunnel, lalu cloudflared meneruskan
        // permintaan ke nginx sebagai http. Tanpa proxy yang dipercaya, Laravel
        // mengabaikan X-Forwarded-Proto sehingga URL aset dibuat dengan http dan
        // browser memblokirnya sebagai mixed content. Hanya cloudflared yang bisa
        // menjangkau nginx (port tidak di-publish ke host), jadi mempercayai
        // semua proxy aman di sini.
        $middleware->trustProxies(at: '*');

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->alias([
            'shelf.username' => EnsureUsernameIsSet::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
