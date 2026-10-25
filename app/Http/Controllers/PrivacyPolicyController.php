<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PrivacyPolicyController extends Controller
{
    /**
     * Kebijakan privasi: data yang dikumpulkan dan cara mengelolanya.
     */
    public function __invoke(): Response
    {
        return Inertia::render('legal/Privacy', [
            'canonical' => route('legal.privacy'),
            'updatedAt' => now()->translatedFormat('F Y'),
        ]);
    }
}
