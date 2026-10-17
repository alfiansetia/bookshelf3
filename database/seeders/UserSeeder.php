<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Membuat akun contoh supaya rak publik bisa langsung dilihat saat development.
 */
class UserSeeder extends Seeder
{
    /**
     * Akun demo: username => atribut profil.
     *
     * @var array<string, array<string, mixed>>
     */
    private const DEMO_USERS = [
        'demo' => [
            'name' => 'Alya Prameswari',
            'email' => 'demo@bookshelf.test',
            'tagline' => 'Pembaca sastra Indonesia dan Jepang.',
            'bio' => 'Mencatat setiap buku yang selesai dibaca sejak 2019. Suka novel bertema keluarga, sejarah, dan cerita yang bikin mikir lama setelah halaman terakhir.',
            'theme' => 'wood',
            'social_instagram' => 'alyapram',
            'social_x' => 'alyapram',
            'social_youtube' => '@alyabacabuku',
            'social_linkedin' => 'alyaprameswari',
            'social_website' => 'https://alyapram.example.com',
        ],
        'rak-senja' => [
            'name' => 'Bagas Nugroho',
            'email' => 'bagas@bookshelf.test',
            'tagline' => 'Non-fiksi, desain, dan psikologi.',
            'bio' => 'Product designer yang percaya buku non-fiksi adalah cara termurah buat upgrade cara berpikir. Rak ini isinya buku yang sudah lulus seleksi ketat.',
            'theme' => 'minimal',
            'social_instagram' => 'bagasngrh',
            'social_tiktok' => 'bagasngrh',
            'social_x' => 'bagasnugroho',
            'social_website' => 'https://bagas.example.com',
        ],
        'neon-reader' => [
            'name' => 'Citra Larasati',
            'email' => 'citra@bookshelf.test',
            'tagline' => 'Sci-fi, misteri, dan cerita aneh.',
            'bio' => 'Suka buku dengan premis yang bikin kepala berasap. Kalau ceritanya bikin insomnia, kemungkinan langsung masuk daftar favorit.',
            'theme' => 'neon',
            'social_instagram' => 'citralrs',
            'social_youtube' => '@citrabacabuku',
            'social_x' => 'citralarasati',
        ],
        'kopi-dan-buku' => [
            'name' => 'Damar Wicaksono',
            'email' => 'damar@bookshelf.test',
            'tagline' => 'Rak santai ditemani kopi tubruk.',
            'bio' => 'Membaca pelan tapi konsisten. Rak ini untuk kawan-kawan yang ingin rekomendasi bacaan ringan sampai sedang, tanpa harus buru-buru.',
            'theme' => 'cozy',
            'social_tiktok' => 'damarbaca',
            'social_instagram' => 'damar.w',
            'social_website' => 'https://damar.example.com',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::DEMO_USERS as $username => $attributes) {
            User::query()->updateOrCreate(
                ['username' => $username],
                [
                    ...$attributes,
                    'google_id' => 'demo-'.Str::random(16),
                    'email_verified_at' => now(),
                    'is_public' => true,
                ],
            );
        }
    }
}
