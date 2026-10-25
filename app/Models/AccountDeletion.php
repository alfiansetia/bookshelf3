<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * Jejak minimal akun yang dihapus pengguna sendiri.
 *
 * Baris ini BUKAN data pribadi lengkap: hanya google_id (kunci penolak
 * daftar ulang), email, dan username terakhir. Dipakai untuk menegakkan
 * masa tenggang daftar ulang dan reservasi username di
 * `config('bookshelf.deletion.cooldown_days')`.
 *
 * @property int $id
 * @property string $google_id
 * @property string $email
 * @property string|null $username
 * @property CarbonImmutable|null $deleted_at
 */
class AccountDeletion extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = ['google_id', 'email', 'username', 'deleted_at'];

    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deleted_at' => 'immutable_datetime',
        ];
    }

    /**
     * Masa tenggang daftar ulang dalam hari.
     */
    public static function cooldownDays(): int
    {
        return (int) config('bookshelf.deletion.cooldown_days');
    }

    /**
     * Apakah masa tenggang masih berlaku.
     */
    public function isActive(): bool
    {
        return $this->availableAt()?->isFuture() ?? false;
    }

    /**
     * Kapan google_id ini boleh dipakai daftar lagi (null bila sudah boleh).
     */
    public function availableAt(): ?CarbonImmutable
    {
        if ($this->deleted_at === null) {
            return null;
        }

        $availableAt = $this->deleted_at->addDays(self::cooldownDays());

        return $availableAt->isFuture() ? $availableAt : null;
    }
}
