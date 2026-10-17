<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bahasa Buku
    |--------------------------------------------------------------------------
    |
    | Sumber tunggal (single source of truth) daftar bahasa buku yang bisa
    | dipilih pemilik rak dan dipakai sebagai filter di halaman publik.
    | Key = kode bahasa, value = label Bahasa Indonesia.
    |
    */

    'languages' => [
        'id' => 'Indonesia',
        'en' => 'Inggris',
        'ms' => 'Melayu',
        'jv' => 'Jawa',
        'su' => 'Sunda',
        'ar' => 'Arab',
        'zh' => 'Mandarin',
        'ja' => 'Jepang',
        'ko' => 'Korea',
        'hi' => 'Hindi',
        'th' => 'Thailand',
        'vi' => 'Vietnam',
        'nl' => 'Belanda',
        'de' => 'Jerman',
        'fr' => 'Prancis',
        'es' => 'Spanyol',
        'it' => 'Italia',
        'pt' => 'Portugis',
        'ru' => 'Rusia',
        'tr' => 'Turki',
        'fa' => 'Persia',
        'sv' => 'Swedia',
        'other' => 'Lainnya',
    ],

    /*
    |--------------------------------------------------------------------------
    | Username Publik
    |--------------------------------------------------------------------------
    |
    | Username dipakai langsung sebagai alamat rak: example.com/username.
    | Daftar `reserved` mencegah username menabrak route aplikasi.
    |
    */

    'username' => [
        'min' => 3,
        'max' => 30,
        'regex' => '/^[a-z0-9](?:[a-z0-9._-]{1,28}[a-z0-9])$/',
        'reserved' => [
            'about', 'admin', 'administrator', 'api', 'app', 'appearance', 'assets',
            'auth', 'blog', 'books', 'build', 'buku', 'cdn', 'cookie', 'css',
            'dashboard', 'docs', 'email', 'explore', 'favicon', 'favicon.ico',
            'feedback', 'google',
            'health', 'help', 'home', 'horizon', 'icons', 'images', 'img', 'jelajah',
            'js', 'katalog', 'kebijakan-cookie', 'legal', 'livewire', 'login',
            'logout', 'mail', 'manifest', 'manifest.webmanifest', 'me', 'new',
            'nova', 'oauth', 'offline',
            'onboarding', 'premium', 'privacy', 'profile', 'profiles', 'pulse', 'rak',
            'register', 'robots', 'robots.txt', 'sanctum', 'search', 'security',
            'settings', 'shelf',
            'shelves', 'sitemap', 'sitemap.xml', 'static', 'status', 'storage',
            'support', 'sw', 'sw.js',
            'telescope', 'terms', 'up', 'user', 'users', 'welcome', 'webmanifest',
            'well-known', 'webhook', 'webhooks', 'www',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Unggahan Sampul & Avatar
    |--------------------------------------------------------------------------
    */

    'cover' => [
        'disk' => env('BOOKSHELF_IMAGE_DISK', 'public'),
        'directory' => 'covers',
        'max_kilobytes' => 3072,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
        'max_source_width' => 6000,
        'max_source_height' => 9000,
        'max_width' => 1000,
        'max_height' => 1500,
        'quality' => 82,
    ],

    'avatar' => [
        'disk' => env('BOOKSHELF_IMAGE_DISK', 'public'),
        'directory' => 'avatars',
        'max_kilobytes' => 2048,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
        'max_source_width' => 6000,
        'max_source_height' => 6000,
        'max_width' => 512,
        'max_height' => 512,
        'quality' => 82,
    ],

    /*
    |--------------------------------------------------------------------------
    | Batas Konten
    |--------------------------------------------------------------------------
    */

    'limits' => [
        'books_per_user' => 500,
        'bio' => 600,
        'tagline' => 120,
        'synopsis' => 6000,
        'review' => 6000,
    ],

    'rating' => [
        'min' => 1,
        'max' => 5,
    ],

    'pagination' => [
        'books' => 12,
        'shelf' => 24,
        'explore' => 12,
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO & Berbagi
    |--------------------------------------------------------------------------
    |
    | Nilai bawaan untuk judul, deskripsi, dan gambar pratinjau saat halaman
    | dibagikan. Dipakai middleware Inertia sebagai data bersama sehingga
    | tag meta selalu memakai URL absolut.
    |
    */

    'seo' => [
        'title_suffix' => env('APP_NAME', 'Rak Buku'),
        'description' => 'Rak buku pribadi yang bisa dibagikan. Simpan buku yang sedang dan sudah kamu baca, tulis catatan serta rating, lalu bagikan raknya lewat satu tautan.',
        'image' => 'images/og-default.png',
        'locale' => 'id_ID',
        'twitter' => null,
        'sitemap_book_limit' => 20000,
    ],

    /*
    |--------------------------------------------------------------------------
    | PWA
    |--------------------------------------------------------------------------
    */

    'pwa' => [
        'name' => env('APP_NAME', 'Rak Buku'),
        'short_name' => env('APP_SHORT_NAME', 'Rak Buku'),
        'theme_color' => '#186751',
        'theme_color_dark' => '#121517',
        'background_color' => '#FBFAF8',
    ],

];
