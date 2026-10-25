<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Pemindai kata kasar untuk data yang sudah tersimpan.
 *
 * Dipakai admin untuk memindai username, profil, dan buku. Logikanya sama
 * dengan `App\Validation\NoProfanity` agar hasil pindaian konsisten dengan
 * aturan validasi: teks bebas memakai batas kata, username/handle memakai
 * substring (kata < 4 huruf dilewati agar nama normal tidak kena).
 */
class ProfanityScanner
{
    /**
     * @return array<int, string>
     */
    public function words(): array
    {
        /** @var array<int, string> $words */
        $words = config()->array('bookshelf.profanity.words', []);

        $words = array_values(array_unique(array_filter(array_map(
            fn (mixed $word): string => mb_strtolower(trim((string) $word)),
            $words,
        ))));

        return $words;
    }

    /**
     * Cari kata kasar pada teks bebas (batas kata).
     *
     * @return array<int, string>
     */
    public function findInText(?string $value): array
    {
        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $words = $this->words();

        if ($words === []) {
            return [];
        }

        $lower = mb_strtolower($value);
        $found = [];

        foreach ($words as $word) {
            if (preg_match('/\b'.preg_quote($word, '/').'\b/iu', $lower) === 1) {
                $found[] = $word;
            }
        }

        sort($found);

        return array_values(array_unique($found));
    }

    /**
     * Cari kata kasar pada username/handle (substring, abaikan . _ - spasi).
     *
     * @return array<int, string>
     */
    public function findInHandle(?string $value): array
    {
        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $compact = Str::of(mb_strtolower($value))->replace(['.', '_', '-', ' '], '')->value();
        $found = [];

        foreach ($this->words() as $word) {
            $clean = str_replace(['.', '_', '-', ' '], '', $word);

            if ($clean === '' || mb_strlen($clean) < 4) {
                continue;
            }

            if (str_contains($compact, $clean)) {
                $found[] = $word;
            }
        }

        sort($found);

        return array_values(array_unique($found));
    }

    /**
     * Potongan teks di sekitar temuan pertama agar mudah dibaca admin.
     */
    public function snippet(?string $value, int $limit = 140): string
    {
        if (! is_string($value) || trim($value) === '') {
            return '';
        }

        return Str::limit(trim(preg_replace('/\s+/', ' ', $value) ?? ''), $limit);
    }
}
