<?php

namespace App\Concerns;

use App\BookStatus;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Logika bersama untuk membaca filter, hitungan status, dan ringkasan rak.
 *
 * Dipakai oleh rak pribadi pemilik maupun halaman rak publik.
 */
trait FiltersShelfBooks
{
    /**
     * Ambil filter dari query string dan buang nilai yang tidak dikenal.
     *
     * @return array{search: string|null, status: string|null, language: string|null, sort: string, favorite: bool}
     */
    protected function shelfFiltersFrom(Request $request): array
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $language = $request->query('language');
        $sort = $request->query('sort');

        return [
            'search' => is_string($search) && trim($search) !== '' ? trim($search) : null,
            'status' => is_string($status) && BookStatus::tryFrom($status) !== null ? $status : null,
            'language' => is_string($language) && array_key_exists($language, config('bookshelf.languages'))
                ? $language
                : null,
            'sort' => is_string($sort) && in_array($sort, array_column(Book::sortOptions(), 'value'), true)
                ? $sort
                : 'recent',
            'favorite' => $request->boolean('favorite'),
        ];
    }

    /**
     * Terapkan filter yang sudah dibersihkan ke query buku.
     *
     * @param  Builder<Book>  $query
     * @param  array{search: string|null, status: string|null, language: string|null, sort: string, favorite: bool}  $filters
     * @return Builder<Book>
     */
    protected function applyShelfFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->search($filters['search'])
            ->withStatus($filters['status'])
            ->inLanguage($filters['language'])
            ->when($filters['favorite'], fn (Builder $query): Builder => $query->favorites())
            ->sorted($filters['sort']);
    }

    /**
     * Jumlah buku per status pada sebuah rak.
     *
     * @param  Builder<Book>  $base
     * @return array<string, int>
     */
    protected function bookStatusCounts(Builder $base): array
    {
        $counts = $base
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return Collection::make(BookStatus::cases())
            ->mapWithKeys(fn (BookStatus $status): array => [
                $status->value => (int) $counts->get($status->value, 0),
            ])
            ->all();
    }

    /**
     * Ringkasan angka rak: total, per status, favorit, halaman, rating rata-rata.
     *
     * @param  Builder<Book>  $base
     * @return array{total: int, finished: int, reading: int, want_to_read: int, on_hold: int, dropped: int, favorites: int, pages_read: int, average_rating: float}
     */
    protected function shelfSummary(Builder $base): array
    {
        $statusCounts = $this->bookStatusCounts(clone $base);

        return [
            'total' => array_sum($statusCounts),
            'finished' => $statusCounts[BookStatus::Finished->value] ?? 0,
            'reading' => $statusCounts[BookStatus::Reading->value] ?? 0,
            'want_to_read' => $statusCounts[BookStatus::WantToRead->value] ?? 0,
            'on_hold' => $statusCounts[BookStatus::OnHold->value] ?? 0,
            'dropped' => $statusCounts[BookStatus::Dropped->value] ?? 0,
            'favorites' => (clone $base)->favorites()->count(),
            'pages_read' => (int) (clone $base)->withStatus(BookStatus::Finished)->sum('total_pages'),
            'average_rating' => round((float) (clone $base)->whereNotNull('rating')->avg('rating'), 1),
        ];
    }

    /**
     * Kelompokkan buku menjadi baris rak per status baca.
     *
     * @param  Collection<int, Book>  $books
     * @return array<int, array{status: string, label: string, color: string, icon: string, total: int, books: array<int, array<string, mixed>>}>
     */
    protected function shelfRows(Collection $books): array
    {
        $rows = [];

        foreach (BookStatus::ordered() as $status) {
            $grouped = $books->filter(fn (Book $book): bool => $book->status === $status);

            if ($grouped->isEmpty()) {
                continue;
            }

            $rows[] = [
                'status' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
                'icon' => $status->icon(),
                'total' => $grouped->count(),
                'books' => $grouped->values()
                    ->map(fn (Book $book): array => BookResource::forShelf($book))
                    ->all(),
            ];
        }

        return $rows;
    }

    /**
     * Daftar bahasa yang benar-benar ada di rak beserta jumlah bukunya.
     *
     * @param  Builder<Book>  $base
     * @return array<int, array{value: string, label: string, total: int}>
     */
    protected function bookLanguagesInShelf(Builder $base): array
    {
        $counts = $base
            ->selectRaw('language, count(*) as total')
            ->groupBy('language')
            ->pluck('total', 'language');

        $languages = [];

        foreach ($counts->sortDesc() as $code => $total) {
            $languages[] = [
                'value' => (string) $code,
                'label' => (string) (config('bookshelf.languages.'.$code) ?? config('bookshelf.languages.other')),
                'total' => (int) $total,
            ];
        }

        return $languages;
    }
}
