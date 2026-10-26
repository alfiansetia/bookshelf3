<?php

namespace App;

/**
 * Visibilitas rak buku milik pengguna.
 *
 * - public: tampil di jelajah/sitemap, bisa dibuka lewat direct link.
 * - unlisted: disembunyikan dari jelajah/sitemap, tetap bisa dibuka lewat direct link.
 * - private: hanya pemilik yang bisa membuka, pengunjung lain melihat halaman Private.
 */
enum ShelfVisibility: string
{
    case Public = 'public';
    case Unlisted = 'unlisted';
    case Private = 'private';

    /**
     * Nama opsi yang tampil di pengaturan.
     */
    public function label(): string
    {
        return match ($this) {
            self::Public => 'Publik',
            self::Unlisted => 'Unlisted',
            self::Private => 'Privat',
        };
    }

    /**
     * Penjelasan singkat tiap opsi.
     */
    public function description(): string
    {
        return match ($this) {
            self::Public => 'Tampil di halaman jelajah dan bisa dibuka siapa pun lewat link.',
            self::Unlisted => 'Disembunyikan dari jelajah, tapi tetap bisa dibuka siapa pun yang punya link.',
            self::Private => 'Hanya kamu yang bisa membuka. Pengunjung lain melihat halaman privat.',
        };
    }

    /**
     * Nilai default untuk pengguna baru.
     */
    public static function default(): self
    {
        return self::Public;
    }

    /**
     * Semua opsi dalam bentuk array yang siap dikirim ke frontend.
     *
     * @return array<int, array{value: string, label: string, description: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $visibility): array => [
            'value' => $visibility->value,
            'label' => $visibility->label(),
            'description' => $visibility->description(),
        ], self::cases());
    }
}
