<?php

namespace App\Concerns;

use App\Models\Book;
use Illuminate\Support\Str;

/**
 * Membuat slug buku yang unik per pemilik rak.
 *
 * Dipakai Book agar URL detail buku berbentuk /username/judul-buku.
 */
trait GeneratesBookSlugs
{
    /**
     * Daftarkan hook pembuatan slug.
     */
    protected static function bootGeneratesBookSlugs(): void
    {
        static::saving(function (Book $book): void {
            $shouldGenerate = blank($book->slug)
                || ($book->exists && $book->isDirty('title'));

            if (! $shouldGenerate) {
                return;
            }

            $book->slug = $book->buildUniqueSlug(
                (string) $book->title,
                (int) $book->user_id,
                $book->getKey(),
            );
        });
    }

    /**
     * Bentuk slug unik untuk judul dan pemilik rak tertentu.
     */
    protected function buildUniqueSlug(string $title, int $userId, ?int $ignoreId = null): string
    {
        $base = (string) Str::limit(Str::slug($title) ?: 'buku', 60, '');
        $slug = $base;
        $suffix = 2;

        while ($this->slugIsTaken($slug, $userId, $ignoreId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * Cek apakah slug sudah dipakai buku lain milik pengguna yang sama.
     */
    protected function slugIsTaken(string $slug, int $userId, ?int $ignoreId = null): bool
    {
        if ($userId === 0) {
            return false;
        }

        return static::query()
            ->where('user_id', $userId)
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }
}
