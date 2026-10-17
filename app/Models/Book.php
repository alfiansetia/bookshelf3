<?php

namespace App\Models;

use App\BookStatus;
use App\Concerns\GeneratesBookSlugs;
use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string $author
 * @property string|null $publisher
 * @property int|null $published_year
 * @property string $language
 * @property BookStatus $status
 * @property int|null $rating
 * @property string|null $synopsis
 * @property string|null $review
 * @property string|null $cover_path
 * @property int|null $total_pages
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 * @property bool $is_favorite
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $cover_url
 * @property-read string $language_label
 * @property-read User $user
 */
#[Fillable([
    'user_id', 'title', 'slug', 'author', 'publisher', 'published_year', 'language',
    'status', 'rating', 'synopsis', 'review', 'cover_path', 'total_pages',
    'started_at', 'finished_at', 'is_favorite', 'position',
])]
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use GeneratesBookSlugs, HasFactory;

    /**
     * Atribut turunan yang ikut dikirim ke frontend.
     *
     * @var list<string>
     */
    protected $appends = ['cover_url', 'language_label'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BookStatus::class,
            'rating' => 'integer',
            'published_year' => 'integer',
            'total_pages' => 'integer',
            'position' => 'integer',
            'is_favorite' => 'boolean',
            'started_at' => 'date',
            'finished_at' => 'date',
        ];
    }

    /**
     * Pemilik rak buku ini.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * URL publik sampul depan buku.
     *
     * @return Attribute<string|null, never>
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->cover_path === null
                ? null
                : Storage::disk(config('bookshelf.cover.disk'))->url($this->cover_path),
        );
    }

    /**
     * Label bahasa buku sesuai konfigurasi aplikasi.
     *
     * @return Attribute<string, never>
     */
    protected function languageLabel(): Attribute
    {
        return Attribute::get(
            fn (): string => config("bookshelf.languages.{$this->language}")
                ?? (string) config('bookshelf.languages.other'),
        );
    }

    /**
     * Apakah buku ini dimiliki pengguna tertentu.
     */
    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && $this->user_id === $user->id;
    }

    /**
     * Apakah buku punya sampul yang tersimpan.
     */
    public function hasCover(): bool
    {
        return filled($this->cover_path);
    }

    /**
     * Filter pencarian judul, penulis, atau penerbit.
     *
     * @param  Builder<Book>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = is_string($term) ? trim($term) : '';

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $query) use ($term): void {
            $like = '%'.$term.'%';

            $query->where('title', 'like', $like)
                ->orWhere('author', 'like', $like)
                ->orWhere('publisher', 'like', $like);
        });
    }

    /**
     * Filter berdasarkan satu status baca.
     *
     * @param  Builder<Book>  $query
     */
    public function scopeWithStatus(Builder $query, BookStatus|string|null $status): void
    {
        if (blank($status)) {
            return;
        }

        $query->where('status', $status instanceof BookStatus ? $status->value : $status);
    }

    /**
     * Filter berdasarkan kode bahasa buku.
     *
     * @param  Builder<Book>  $query
     */
    public function scopeInLanguage(Builder $query, ?string $language): void
    {
        if (blank($language)) {
            return;
        }

        $query->where('language', $language);
    }

    /**
     * Hanya buku yang ditandai favorit.
     *
     * @param  Builder<Book>  $query
     */
    public function scopeFavorites(Builder $query): void
    {
        $query->where('is_favorite', true);
    }

    /**
     * Terapkan urutan tampilan yang dipilih pengguna.
     *
     * @param  Builder<Book>  $query
     */
    public function scopeSorted(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'title' => $query->orderBy('title'),
            'author' => $query->orderBy('author'),
            'rating' => $query->orderByDesc('rating')->orderByDesc('created_at'),
            'year' => $query->orderByDesc('published_year'),
            'oldest' => $query->orderBy('created_at'),
            'finished' => $query->orderByDesc('finished_at')->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'),
        };
    }

    /**
     * Pilihan pengurutan untuk dropdown di frontend.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function sortOptions(): array
    {
        return [
            ['value' => 'recent', 'label' => 'Terbaru ditambahkan'],
            ['value' => 'oldest', 'label' => 'Terlama ditambahkan'],
            ['value' => 'title', 'label' => 'Judul A-Z'],
            ['value' => 'author', 'label' => 'Penulis A-Z'],
            ['value' => 'rating', 'label' => 'Rating tertinggi'],
            ['value' => 'year', 'label' => 'Tahun terbit terbaru'],
            ['value' => 'finished', 'label' => 'Terakhir tamat'],
        ];
    }

    /**
     * Pilihan bahasa buku untuk dropdown filter.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function languageOptions(): array
    {
        $options = [];

        foreach (config('bookshelf.languages', []) as $value => $label) {
            $options[] = ['value' => (string) $value, 'label' => $label];
        }

        return $options;
    }
}
