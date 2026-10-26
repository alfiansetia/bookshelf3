<?php

namespace App\Models;

use App\ShelfTheme;
use App\ShelfVisibility;
use App\SocialPlatform;
use App\UserRole;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $username
 * @property CarbonImmutable|null $username_changed_at
 * @property string $google_id
 * @property string|null $avatar_path
 * @property CarbonImmutable|null $avatar_removed_at
 * @property string|null $tagline
 * @property string|null $bio
 * @property ShelfTheme|null $theme
 * @property ShelfVisibility $visibility
 * @property string|null $social_instagram
 * @property string|null $social_tiktok
 * @property string|null $social_x
 * @property string|null $social_youtube
 * @property string|null $social_linkedin
 * @property string|null $social_website
 * @property UserRole $role
 * @property CarbonImmutable|null $blocked_at
 * @property string|null $blocked_reason
 * @property int|null $blocked_by_id
 * @property Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $blockedBy
 * @property-read string|null $avatar
 * @property-read string|null $avatar_url
 * @property-read int|null $books_count
 * @property-read int|null $finished_count
 */
#[Fillable([
    'name', 'email', 'username', 'google_id', 'avatar_path', 'tagline',
    'bio', 'theme', 'visibility', 'social_instagram', 'social_tiktok', 'social_x',
    'social_youtube', 'social_linkedin', 'social_website',
    'role', 'blocked_at', 'blocked_reason', 'blocked_by_id',
])]
#[Hidden(['remember_token', 'google_id', 'avatar_path'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Atribut turunan yang ikut dikirim ke frontend.
     *
     * @var list<string>
     */
    protected $appends = ['avatar'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'username_changed_at' => 'datetime',
            'avatar_removed_at' => 'datetime',
            'blocked_at' => 'datetime',
            'theme' => ShelfTheme::class,
            'role' => UserRole::class,
            'visibility' => ShelfVisibility::class,
        ];
    }

    /**
     * Seluruh buku pada rak pengguna ini.
     *
     * @return HasMany<Book, $this>
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    /**
     * Admin yang memblokir akun ini (null bila belum pernah diblokir).
     *
     * @return BelongsTo<User, $this>
     */
    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'blocked_by_id');
    }

    /**
     * URL avatar pengguna (null bila memakai inisial nama).
     *
     * @return Attribute<string|null, never>
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->avatar_path === null
                ? null
                : Storage::disk(config('bookshelf.avatar.disk'))->url($this->avatar_path),
        );
    }

    /**
     * Alias `avatar` agar komponen bawaan starter kit tetap bekerja.
     *
     * @return Attribute<string|null, never>
     */
    protected function avatar(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->avatar_url);
    }

    /**
     * Apakah pengguna sudah memilih username publik.
     */
    public function hasUsername(): bool
    {
        return filled($this->username);
    }

    /**
     * Apakah akun ini punya akses admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Apakah akun ini sedang diblokir.
     */
    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    /**
     * Alasan blokir yang boleh dibaca pengguna dan pengunjung raknya.
     */
    public function blockedMessage(): string
    {
        if (! $this->isBlocked()) {
            return '';
        }

        return filled($this->blocked_reason)
            ? (string) $this->blocked_reason
            : 'Tidak ada keterangan tambahan dari admin.';
    }

    /**
     * Blokir akun ini disertai alasannya.
     */
    public function block(string $reason, ?self $admin = null): void
    {
        $this->forceFill([
            'blocked_at' => now(),
            'blocked_reason' => $reason,
            'blocked_by_id' => $admin?->getKey(),
        ])->save();
    }

    /**
     * Buka blokir akun ini.
     */
    public function unblock(): void
    {
        $this->forceFill([
            'blocked_at' => null,
            'blocked_reason' => null,
            'blocked_by_id' => null,
        ])->save();
    }

    /**
     * Apakah pengguna sudah boleh mengganti username lagi.
     *
     * Username dibatasi agar alamat rak tidak berpindah-pindah terlalu sering.
     */
    public function canChangeUsername(): bool
    {
        return $this->usernameChangeAvailableAt() === null;
    }

    /**
     * Kapan username boleh diganti lagi (null bila sudah boleh sekarang).
     */
    public function usernameChangeAvailableAt(): ?CarbonImmutable
    {
        if ($this->username_changed_at === null) {
            return null;
        }

        $availableAt = $this->username_changed_at->addDays(
            (int) config('bookshelf.username.cooldown_days'),
        );

        return $availableAt->isFuture() ? $availableAt : null;
    }

    /**
     * Apakah rak tampil di jelajah (publik + punya username).
     */
    public function shelfIsPublic(): bool
    {
        return $this->visibility === ShelfVisibility::Public && $this->hasUsername();
    }

    /**
     * Apakah rak bisa dibuka lewat direct link oleh non-owner.
     *
     * Berlaku untuk public dan unlisted yang sudah punya username.
     */
    public function shelfIsAccessibleViaLink(): bool
    {
        return in_array($this->visibility, [ShelfVisibility::Public, ShelfVisibility::Unlisted], true)
            && $this->hasUsername();
    }

    /**
     * Apakah rak berstatus unlisted.
     */
    public function shelfIsUnlisted(): bool
    {
        return $this->visibility === ShelfVisibility::Unlisted && $this->hasUsername();
    }

    /**
     * Apakah rak berstatus privat (hanya pemilik).
     */
    public function shelfIsPrivate(): bool
    {
        return $this->visibility === ShelfVisibility::Private;
    }

    /**
     * Kompatibilitas baca untuk kode lama yang memakai is_public.
     *
     * true hanya untuk rak publik, false untuk unlisted dan privat.
     */
    public function getIsPublicAttribute(): bool
    {
        return $this->visibility === ShelfVisibility::Public;
    }

    /**
     * Tema rak yang dipakai pengguna.
     */
    public function shelfTheme(): ShelfTheme
    {
        return $this->theme ?? ShelfTheme::default();
    }

    /**
     * Daftar tautan sosial yang sudah diisi pengguna.
     *
     * @return array<int, array{platform: string, label: string, icon: string, handle: string, url: string}>
     */
    public function socialProfiles(): array
    {
        $profiles = [];

        foreach (SocialPlatform::cases() as $platform) {
            $value = $this->getAttribute($platform->column());

            if (blank($value) || ! is_string($value)) {
                continue;
            }

            $url = $platform->profileUrl($value);

            if ($url === null) {
                continue;
            }

            $profiles[] = [
                'platform' => $platform->value,
                'label' => $platform->label(),
                'icon' => $platform->icon(),
                'handle' => $platform->isUsernameOnly() ? ltrim($value, '@') : $value,
                'url' => $url,
            ];
        }

        return $profiles;
    }

    /**
     * Rak yang tampil di jelajah: hanya yang publik dan punya username.
     *
     * Rak unlisted dan privat disembunyikan dari jelajah, halaman depan, dan sitemap.
     * Akun yang diblokir disembunyikan dari jelajah, halaman depan, dan sitemap.
     *
     * @param  Builder<User>  $query
     */
    public function scopePublicShelf(Builder $query): void
    {
        $query->where('visibility', ShelfVisibility::Public)
            ->whereNotNull('username')
            ->whereNull('blocked_at');
    }

    /**
     * Cari pemilik rak berdasarkan username.
     *
     * @param  Builder<User>  $query
     */
    public function scopeWhereUsername(Builder $query, string $username): void
    {
        $query->where('username', $username);
    }
}
