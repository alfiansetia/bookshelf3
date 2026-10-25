<?php

namespace App\Validation;

use App\Models\AccountDeletion;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Menolak username yang masih dalam masa tenggang hapus akun.
 *
 * Username akun yang dihapus dicadangkan selama
 * `config('bookshelf.deletion.cooldown_days')` agar tidak bisa langsung
 * dipakai orang lain maupun pemilik lamanya (lihat AccountDeletion).
 */
class UsernameNotRecentlyDeleted implements ValidationRule
{
    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $tombstone = AccountDeletion::query()
            ->where('username', mb_strtolower($value))
            ->where('deleted_at', '>', now()->subDays(AccountDeletion::cooldownDays()))
            ->first();

        if ($tombstone instanceof AccountDeletion && $tombstone->isActive()) {
            $fail(sprintf(
                'Username ini baru saja dihapus dan belum bisa dipakai lagi. Coba lagi setelah %s.',
                $tombstone->availableAt()?->translatedFormat('j F Y') ?? '-',
            ));
        }
    }
}
