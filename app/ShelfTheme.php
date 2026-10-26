<?php

namespace App;

/**
 * Gaya tampilan rak buku yang dipilih pemilik rak.
 */
enum ShelfTheme: string
{
    case Wood = 'wood';
    case Minimal = 'minimal';
    case Neon = 'neon';
    case Cozy = 'cozy';
    case Story = 'story';
    case Catalog = 'catalog';

    /**
     * Nama gaya rak.
     */
    public function label(): string
    {
        return match ($this) {
            self::Wood => 'Rak Kayu Klasik',
            self::Minimal => 'Rak Minimalis',
            self::Neon => 'Rak Neon',
            self::Cozy => 'Rak Kafe Hangat',
            self::Story => 'Rak Reel Cerita',
            self::Catalog => 'Kartu Katalog',
        };
    }

    /**
     * Satu baris pemasaran untuk pemilih tema.
     */
    public function tagline(): string
    {
        return match ($this) {
            self::Wood => 'Buku berdiri berdampingan di atas papan kayu, judul terbaca di punggungnya.',
            self::Minimal => 'Daftar bacaan rapi seperti indeks buku, tanpa sampul besar.',
            self::Neon => 'Kisi kotak gelap yang menyala seperti panel digital.',
            self::Cozy => 'Papan tempel berisi polaroid buku bernuansa kafe.',
            self::Story => 'Kartu cerita vertikal full-cover yang digeser seperti story, mobile-first.',
            self::Catalog => 'Kartu katalog perpustakaan bernomor dengan stempel status, mobile-first.',
        };
    }

    /**
     * Penjelasan lebih panjang untuk halaman pengaturan rak.
     */
    public function description(): string
    {
        return match ($this) {
            self::Wood => 'Di desktop buku berdiri berdampingan di atas papan kayu dengan judul di punggungnya, lengkap dengan kartu pratinjau saat disorot. Di ponsel buku tersusun ke bawah dengan sampul kecil.',
            self::Minimal => 'Daftar indeks bergaris tipis: nomor urut, sampul mungil, judul, penulis, tahun, dan rating. Dua kolom di desktop, satu kolom di ponsel.',
            self::Neon => 'Latar gelap dengan kisi kotak berisi sampul penuh dan pendar warna. Di ponsel tampil sebagai kisi 2 kolom yang padat.',
            self::Cozy => 'Kartu polaroid bersudut miring ditempel di latar kertas hangat, tersusun beberapa kolom di desktop dan dua kolom di ponsel.',
            self::Story => 'Setiap status jadi strip reel horizontal: kartu 9:14 full-cover dengan judul di atas gradient, digeser dengan snap. Sama di ponsel dan desktop, tanpa pratinjau hover.',
            self::Catalog => 'Kartu katalog ala perpustakaan: nomor urut, sampul kecil, judul, penulis, dan stempel status. Satu kolom di ponsel, dua kolom di tablet, tiga kolom di desktop.',
        };
    }

    /**
     * Apakah tema memaksa permukaan gelap tanpa mengikuti preferensi pengunjung.
     */
    public function isDark(): bool
    {
        return $this === self::Neon || $this === self::Story;
    }

    /**
     * Warna aksen tema (hex) untuk pratinjau dan meta theme-color.
     */
    public function accent(): string
    {
        return match ($this) {
            self::Wood => '#b4763a',
            self::Minimal => '#0f172a',
            self::Neon => '#22d3ee',
            self::Cozy => '#e08a9a',
            self::Story => '#f59e0b',
            self::Catalog => '#4f46e5',
        };
    }

    /**
     * Tema default untuk pengguna baru.
     */
    public static function default(): self
    {
        return self::Wood;
    }

    /**
     * Semua tema dalam bentuk array yang siap dikirim ke frontend.
     *
     * @return array<int, array{value: string, label: string, tagline: string, description: string, is_dark: bool, accent: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $theme): array => [
            'value' => $theme->value,
            'label' => $theme->label(),
            'tagline' => $theme->tagline(),
            'description' => $theme->description(),
            'is_dark' => $theme->isDark(),
            'accent' => $theme->accent(),
        ], self::cases());
    }

    /**
     * Pilihan tema untuk dropdown (value => label).
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (self::cases() as $theme) {
            $labels[$theme->value] = $theme->label();
        }

        return $labels;
    }
}
