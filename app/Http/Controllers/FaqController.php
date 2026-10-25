<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    /**
     * Halaman pertanyaan yang sering ditanyakan.
     */
    public function __invoke(): Response
    {
        return Inertia::render('legal/Faq', [
            'canonical' => route('legal.faq'),
            'updatedAt' => now()->translatedFormat('F Y'),
        ]);
    }
}
