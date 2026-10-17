<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CookiePolicyController extends Controller
{
    /**
     * Penjelasan kategori cookie yang dipakai aplikasi.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('legal/Cookie', [
            'sessionCookie' => config('session.cookie'),
            'canonical' => route('legal.cookie'),
            'updatedAt' => now()->translatedFormat('F Y'),
        ]);
    }
}
