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
    | Daftar `reserved` mencegah username menabrak route aplikasi. Pemilik rak
    | hanya boleh mengganti username sekali setiap `cooldown_days` hari.
    |
    */

    'username' => [
        'min' => 3,
        'max' => 30,
        'cooldown_days' => 3,
        'regex' => '/^[a-z0-9](?:[a-z0-9._-]{1,28}[a-z0-9])$/',
        'reserved' => [
            'about', 'admin', 'administrator', 'api', 'app', 'appearance', 'assets',
            'auth', 'blog', 'books', 'build', 'buku', 'cdn', 'cookie', 'css',
            'dashboard', 'docs', 'email', 'explore', 'faq', 'favicon', 'favicon.ico',
            'feedback', 'google',
            'health', 'help', 'home', 'horizon', 'icons', 'images', 'img', 'jelajah',
            'js', 'katalog', 'kebijakan-cookie', 'kebijakan-privasi', 'kontak', 'legal', 'livewire', 'login',
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

    /*
    |--------------------------------------------------------------------------
    | Moderasi Pengguna
    |--------------------------------------------------------------------------
    |
    | Batas panjang alasan blokir yang ditulis admin. Alasannya ditampilkan
    | kembali ke pengguna yang diblokir sekaligus ke pengunjung raknya.
    |
    */

    'moderation' => [
        'reason_min' => 5,
        'reason_max' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Hapus Akun & Daftar Ulang
    |--------------------------------------------------------------------------
    |
    | Pengguna boleh menghapus akunnya sendiri, tetapi google_id, email, dan
    | username terakhir dicatat di `account_deletions` sebagai tombstone.
    | Selama `cooldown_days` sejak penghapusan, google_id itu ditolak daftar
    | ulang dan username bekasnya tidak bisa dipakai siapa pun supaya tidak
    | disalahgunakan untuk reset blokir, reset batas buku, atau pencatutan
    | username. Setelah lewat, tombstone dihapus dan pendaftaran dibuka lagi.
    |
    */

    'deletion' => [
        'cooldown_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Filter Kata Kasar
    |--------------------------------------------------------------------------
    |
    | Daftar kata yang ditolak oleh `App\Validation\NoProfanity` pada username,
    | profil, tautan sosial, dan konten buku. Semua kata ditulis huruf kecil;
    | pencocokan tidak peka huruf besar/kecil. Tambahkan kata baru di sini
    | tanpa mengubah kode validasi.
    |
    */

    'profanity' => [
        'words' => [
            // Indonesia — makian dan umpatan umum
            'anjing', 'anjir', 'anjay', 'asu', 'babi', 'bangsat',
            'bajingan', 'brengsek', 'tolol', 'goblok', 'bego', 'dungu',
            'dongo', 'idiot', 'kontol', 'memek', 'ngentot', 'jancuk',
            'cuk', 'keparat', 'laknat', 'sialan', 'kampret', 'bedebah',
            'bejat', 'biadab', 'jahanam', 'kunyuk', 'monyet',
            'lonte', 'pelacur', 'perek', 'setan', 'iblis',
            // Indonesia — vulgar/seksual
            'jembut', 'tempik', 'itil', 'pentil', 'toket', 'tetek',
            'nenen', 'pepek', 'puki', 'titit', 'pantat', 'dubur',
            'tai', 'taik', 'coli', 'colmek', 'sange', 'horny',
            'desah', 'orgasme', 'masturbasi', 'porno', 'bokep',
            // Inggris — makian umum
            'fuck', 'fucking', 'fucker', 'motherfucker', 'shit',
            'bullshit', 'horseshit', 'dipshit', 'dumbass', 'jackass',
            'asshole', 'bitch', 'bastard', 'moron', 'imbecile',
            'retard', 'retarded', 'douche', 'douchebag', 'scumbag',
            'cunt', 'fag', 'faggot', 'dick', 'cock', 'pussy',
            'whore', 'slut', 'slutty',
            'nigga', 'nigger', 'kike', 'chink', 'gook', 'spic',
            // Inggris — vulgar/seksual
            'porn', 'hentai', 'blowjob', 'handjob', 'anal', 'orgasm',
            'masturbate', 'masturbation', 'dildo', 'vibrator', 'clit',
            'boobs', 'tits', 'titties', 'nipple', 'vagina', 'penis',
            'cum', 'orgy', 'escort', 'prostitute', 'rape', 'rapist',
            'pedophile', 'incest', 'bestiality', 'sex',
        ],
    ],

    'rating' => [
        'min' => 1,
        'max' => 5,
        'step' => 0.5,
    ],

    'pagination' => [
        'books' => 12,
        'shelf' => 24,
        'explore' => 12,
        'users' => 20,
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
