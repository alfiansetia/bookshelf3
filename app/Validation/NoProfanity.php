<?php

namespace App\Validation;

use App\Services\ProfanityScanner;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Menolak input yang mengandung kata kasar.
 *
 * Daftar kata diambil dari `config('bookshelf.profanity.words')` sehingga
 * mudah ditambah tanpa mengubah kode. Mode bawaan memakai batas kata
 * (word boundary) agar tidak false-positive seperti "class" yang
 * mengandung "ass". Untuk username/handle yang tanpa spasi, pakai
 * `$matchSubstring: true` agar "anjing123" tetap ketolak.
 */
class NoProfanity implements ValidationRule
{
    public function __construct(
        private bool $matchSubstring = false,
        private ProfanityScanner $scanner = new ProfanityScanner,
    ) {}

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $matched = $this->matchSubstring
            ? $this->scanner->findInHandle($value)
            : $this->scanner->findInText($value);

        if ($matched !== []) {
            $fail('Isian ini mengandung kata yang tidak pantas. Silakan gunakan kata lain.');
        }
    }
}
