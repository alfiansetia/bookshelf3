<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    /**
     * Halaman kontak pemilik aplikasi.
     */
    public function __invoke(): Response
    {
        return Inertia::render('legal/Contact', [
            'canonical' => route('legal.contact'),
            'email' => 'alfian@kacangan.net',
            'socials' => [
                [
                    'platform' => 'tiktok',
                    'label' => 'TikTok',
                    'handle' => '@codewithalfi',
                    'url' => 'https://www.tiktok.com/@codewithalfi',
                ],
                [
                    'platform' => 'instagram',
                    'label' => 'Instagram',
                    'handle' => '@codewithalfi',
                    'url' => 'https://instagram.com/codewithalfi',
                ],
                [
                    'platform' => 'github',
                    'label' => 'GitHub',
                    'handle' => 'alfiansetia',
                    'url' => 'https://github.com/alfiansetia',
                ],
            ],
        ]);
    }
}
