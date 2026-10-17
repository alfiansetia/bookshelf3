<?php

namespace App;

/**
 * Platform sosial yang bisa ditautkan pemilik rak.
 *
 * Sebagian besar platform cukup diisi username saja, situs pribadi diisi URL penuh.
 */
enum SocialPlatform: string
{
    case Instagram = 'instagram';
    case TikTok = 'tiktok';
    case X = 'x';
    case YouTube = 'youtube';
    case LinkedIn = 'linkedin';
    case Website = 'website';

    /**
     * Nama platform untuk ditampilkan.
     */
    public function label(): string
    {
        return match ($this) {
            self::Instagram => 'Instagram',
            self::TikTok => 'TikTok',
            self::X => 'X',
            self::YouTube => 'YouTube',
            self::LinkedIn => 'LinkedIn',
            self::Website => 'Situs Web',
        };
    }

    /**
     * Nama ikon Lucide untuk platform ini.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Instagram => 'Instagram',
            self::TikTok => 'Music2',
            self::X => 'Twitter',
            self::YouTube => 'Youtube',
            self::LinkedIn => 'Linkedin',
            self::Website => 'Globe',
        };
    }

    /**
     * Petunjuk pengisian untuk kolom input.
     */
    public function placeholder(): string
    {
        return match ($this) {
            self::Instagram => 'username.instagram',
            self::TikTok => 'username.tiktok',
            self::X => 'username_x',
            self::YouTube => '@channelkamu',
            self::LinkedIn => 'nama-kamu',
            self::Website => 'https://situskamu.com',
        };
    }

    /**
     * Apakah platform ini selalu memakai username saja.
     */
    public function isUsernameOnly(): bool
    {
        return $this !== self::Website;
    }

    /**
     * Panjang maksimal nilai yang disimpan.
     */
    public function maxLength(): int
    {
        return $this === self::Website ? 255 : 60;
    }

    /**
     * Rapikan nilai yang diketik pengguna sebelum disimpan.
     *
     * Menerima "@username", "username", atau URL profil penuh.
     */
    public function normalizeValue(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if ($this === self::Website) {
            return preg_match('#^https?://#i', $value) === 1 ? $value : 'https://'.$value;
        }

        if (preg_match('#^https?://#i', $value) === 1) {
            $path = trim((string) parse_url($value, PHP_URL_PATH), '/');
            $value = $path === '' ? $value : explode('/', $path)[0];
        }

        return ltrim($value, '@');
    }

    /**
     * Ubah nilai yang diisi pengguna menjadi URL profil yang bisa diklik.
     */
    public function profileUrl(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if ($this === self::Website) {
            $url = preg_match('#^https?://#i', $value) === 1 ? $value : 'https://'.$value;

            return filter_var($url, FILTER_VALIDATE_URL) !== false ? $url : null;
        }

        $handle = ltrim($value, '@');

        if ($handle === '' || preg_match('/^[A-Za-z0-9._-]+$/', $handle) !== 1) {
            return null;
        }

        return $this->handleProfileUrl($handle);
    }

    /**
     * URL profil untuk platform yang memakai username, misalnya `budi` atau `@budi`.
     */
    private function handleProfileUrl(string $handle): ?string
    {
        return match ($this) {
            self::Instagram => 'https://instagram.com/'.$handle,
            self::TikTok => 'https://tiktok.com/@'.$handle,
            self::X => 'https://x.com/'.$handle,
            self::YouTube => 'https://youtube.com/@'.$handle,
            self::LinkedIn => 'https://linkedin.com/in/'.$handle,
            self::Website => null,
        };
    }

    /**
     * Nama kolom penyimpanan di tabel users.
     */
    public function column(): string
    {
        return 'social_'.$this->value;
    }

    /**
     * Metadata platform untuk frontend.
     *
     * @return array<int, array{value: string, label: string, icon: string, placeholder: string, username_only: bool, max_length: int}>
     */
    public static function options(): array
    {
        return array_map(fn (self $platform): array => [
            'value' => $platform->value,
            'label' => $platform->label(),
            'icon' => $platform->icon(),
            'placeholder' => $platform->placeholder(),
            'username_only' => $platform->isUsernameOnly(),
            'max_length' => $platform->maxLength(),
        ], self::cases());
    }
}
