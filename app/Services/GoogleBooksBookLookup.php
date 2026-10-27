<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleBooksBookLookup
{
    public const PROVIDER = 'google';

    private const RESULT_LIMIT = 8;

    /** Ambil lebih banyak untuk recall, lalu saring + urutkan sendiri. */
    private const FETCH_LIMIT = 20;

    /**
     * Ambil metadata mentah dari Google Books, sudah dipetakan ke
     * bentuk yang sama dengan hasil OpenLibrary. Kosong bila tanpa
     * key, gagal, atau tidak ketemu — orchestrator lanjut ke fallback.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetch(string $normalizedQuery): array
    {
        $key = (string) config('services.google_books.key');

        if ($key === '') {
            return [];
        }

        $isIsbn = $this->isIsbnQuery($normalizedQuery);

        try {
            $response = Http::timeout(6)
                ->connectTimeout(4)
                ->retry(1, 300)
                ->withUserAgent('Bookshelf3/1.0')
                ->acceptJson()
                ->get('https://www.googleapis.com/books/v1/volumes', [
                    'q' => $isIsbn
                        ? 'isbn:'.preg_replace('/[^0-9xX]/', '', $normalizedQuery)
                        : $normalizedQuery,
                    'maxResults' => self::FETCH_LIMIT,
                    'langRestrict' => 'id',
                    'printType' => 'books',
                    'key' => $key,
                ]);

            if (! $response->ok()) {
                return [];
            }

            $items = $response->json('items', []);

            $mapped = collect(is_array($items) ? $items : [])
                ->take(self::FETCH_LIMIT)
                ->map(fn (array $item): array => $this->mapItem($item))
                ->filter(fn (array $item): bool => $item['title'] !== '')
                ->values()
                ->all();

            // ISBN = exact, tanpa filter. Selain itu buang hasil yang
            // judul maupun penulisnya tidak mengandung query (Google
            // sering mengembalikan fuzzy match dari isi/deskripsi).
            if (! $isIsbn) {
                $mapped = array_values(array_filter(
                    $mapped,
                    fn (array $item): bool => $this->matchesQuery($item, $normalizedQuery),
                ));
            }

            usort($mapped, fn (array $a, array $b): int => $this->rank($b, $normalizedQuery) <=> $this->rank($a, $normalizedQuery));

            return array_slice($mapped, 0, self::RESULT_LIMIT);
        } catch (\Throwable) {
            return [];
        }
    }

    private function isIsbnQuery(string $query): bool
    {
        $digits = (string) preg_replace('/[^0-9xX]/', '', $query);

        return preg_match('/^\d{9}[\dXx]$|^\d{13}$/', $digits) === 1;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function matchesQuery(array $item, string $query): bool
    {
        $queryWords = $this->queryWords($query);

        // Tanpa kata signifikan (mis. query "a b c"): fallback substring penuh.
        if ($queryWords === []) {
            $title = mb_strtolower((string) ($item['title'] ?? ''));

            return mb_strpos($title, $query) !== false;
        }

        $haystack = $this->haystackWords($item);

        // Tiap kata query wajib cocok persis atau typo ringan (bekisar/berkisar).
        foreach ($queryWords as $word) {
            if (! $this->wordMatches($word, $haystack)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Skor relevansi: judul exact > diawali query > kata cocok persis
     * > typo ringan > cocok penulis.
     *
     * @param  array<string, mixed>  $item
     */
    private function rank(array $item, string $query): int
    {
        $title = mb_strtolower(trim((string) ($item['title'] ?? '')));
        $author = mb_strtolower((string) ($item['author'] ?? ''));

        if ($title === $query) {
            return 100;
        }

        $score = 0;

        if (mb_strpos($title, $query) === 0) {
            $score += 10;
        }

        $haystack = $this->haystackWords($item);

        foreach ($this->queryWords($query) as $word) {
            if (mb_strpos($title, $word) !== false || mb_strpos($author, $word) !== false) {
                $score += 2;
            } elseif ($this->wordMatches($word, $haystack)) {
                $score += 1;
            }
        }

        return $score;
    }

    /**
     * Kata query yang signifikan (pendek seperti "di"/"on" diabaikan).
     *
     * @return list<string>
     */
    private function queryWords(string $query): array
    {
        $words = preg_split('/\s+/', mb_strtolower($query)) ?: [];

        return array_values(array_filter(
            $words,
            fn (string $w): bool => mb_strlen($w) >= 3,
        ));
    }

    /**
     * Kata-kata judul + penulis sebagai bahan pencocokan.
     *
     * @param  array<string, mixed>  $item
     * @return list<string>
     */
    private function haystackWords(array $item): array
    {
        $text = mb_strtolower(trim((string) ($item['title'] ?? '').' '.(string) ($item['author'] ?? '')));
        $words = preg_split('/[\s\-:;,.!?()"\']+/', $text) ?: [];

        return array_values(array_filter($words, fn (string $w): bool => $w !== ''));
    }

    private function wordMatches(string $word, array $haystack): bool
    {
        foreach ($haystack as $candidate) {
            if (mb_strpos($candidate, $word) !== false || mb_strpos($word, $candidate) !== false) {
                return true;
            }

            if ($this->levenshteinAscii($word, $candidate) <= ($this->wordLength($word) <= 4 ? 1 : 2)) {
                return true;
            }
        }

        return false;
    }

    private function wordLength(string $word): int
    {
        return mb_strlen($word);
    }

    private function levenshteinAscii(string $a, string $b): int
    {
        return levenshtein(mb_convert_encoding($a, 'ASCII', 'UTF-8'), mb_convert_encoding($b, 'ASCII', 'UTF-8'));
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function mapItem(array $item): array
    {
        $info = is_array($item['volumeInfo'] ?? null) ? $item['volumeInfo'] : [];

        $authors = is_array($info['authors'] ?? null) ? array_values($info['authors']) : [];
        $identifiers = is_array($info['industryIdentifiers'] ?? null) ? $info['industryIdentifiers'] : [];

        $year = null;

        if (is_string($info['publishedDate'] ?? null) && preg_match('/^(\d{4})/', $info['publishedDate'], $m) === 1) {
            $year = (int) $m[1];
        }

        $pages = is_numeric($info['pageCount'] ?? null) ? (int) $info['pageCount'] : null;

        $language = is_string($info['language'] ?? null)
            && array_key_exists(strtolower($info['language']), config('bookshelf.languages', []))
            ? strtolower($info['language'])
            : null;

        $cover = is_array($info['imageLinks'] ?? null)
            ? ($info['imageLinks']['thumbnail'] ?? $info['imageLinks']['smallThumbnail'] ?? null)
            : null;

        if (is_string($cover)) {
            // Google mengembalikan http + zoom=1 (kecil); paksa https.
            $cover = str_replace('http://', 'https://', $cover);
        }

        return [
            'key' => is_string($item['id'] ?? null) ? 'google:'.$item['id'] : null,
            'title' => is_string($info['title'] ?? null) ? trim($info['title']) : '',
            'author' => is_string($authors[0] ?? null) ? $authors[0] : null,
            'authors' => array_slice(array_filter($authors, 'is_string'), 0, 3),
            'publisher' => is_string($info['publisher'] ?? null) ? trim($info['publisher']) : null,
            'published_year' => $year,
            'total_pages' => $pages,
            'isbn' => $this->pickIsbn($identifiers),
            'language' => $language,
            'cover_id' => null,
            'cover_url' => is_string($cover) ? $cover : null,
            'cover_large_url' => is_string($cover) ? str_replace('zoom=1', 'zoom=0', $cover) : null,
        ];
    }

    /**
     * @param  array<int, mixed>  $identifiers
     */
    private function pickIsbn(array $identifiers): ?string
    {
        $fallback = null;

        foreach ($identifiers as $id) {
            if (! is_array($id) || ! is_string($id['identifier'] ?? null)) {
                continue;
            }

            if (($id['type'] ?? null) === 'ISBN_13') {
                return $id['identifier'];
            }

            $fallback ??= $id['identifier'];
        }

        return $fallback;
    }
}
