<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenLibraryBookLookup
{
    public const PROVIDER = 'openlibrary';

    private const RESULT_LIMIT = 8;

    /**
     * Ambil metadata mentah dari OpenLibrary (tanpa cache; cache
     * dipegang orchestrator BookMetadataLookup).
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetch(string $normalizedQuery): array
    {
        try {
            $response = Http::timeout(6)
                ->connectTimeout(4)
                ->retry(1, 300)
                ->withUserAgent('Bookshelf3/1.0 (contact: admin@bookshelf.local)')
                ->acceptJson()
                ->get('https://openlibrary.org/search.json', [
                    'q' => $normalizedQuery,
                    'limit' => self::RESULT_LIMIT,
                    'fields' => 'key,title,author_name,first_publish_year,publisher,number_of_pages_median,cover_i,isbn,language',
                ]);

            if (! $response->ok()) {
                return [];
            }

            $docs = $response->json('docs', []);

            return collect(is_array($docs) ? $docs : [])
                ->take(self::RESULT_LIMIT)
                ->map(fn (array $doc): array => $this->mapDoc($doc))
                ->filter(fn (array $item): bool => $item['title'] !== '')
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param  array<string, mixed>  $doc
     * @return array<string, mixed>
     */
    private function mapDoc(array $doc): array
    {
        $authors = is_array($doc['author_name'] ?? null) ? array_values($doc['author_name']) : [];
        $publishers = is_array($doc['publisher'] ?? null) ? array_values($doc['publisher']) : [];
        $isbns = is_array($doc['isbn'] ?? null) ? array_values($doc['isbn']) : [];
        $languages = is_array($doc['language'] ?? null) ? array_values($doc['language']) : [];

        $year = is_numeric($doc['first_publish_year'] ?? null)
            ? (int) $doc['first_publish_year']
            : null;

        $pages = is_numeric($doc['number_of_pages_median'] ?? null)
            ? (int) $doc['number_of_pages_median']
            : null;

        $coverId = is_numeric($doc['cover_i'] ?? null) ? (int) $doc['cover_i'] : null;

        return [
            'key' => is_string($doc['key'] ?? null) ? $doc['key'] : null,
            'title' => is_string($doc['title'] ?? null) ? trim($doc['title']) : '',
            'author' => $authors[0] ?? null,
            'authors' => array_slice($authors, 0, 3),
            'publisher' => $publishers[0] ?? null,
            'published_year' => $year,
            'total_pages' => $pages,
            'isbn' => $isbns[0] ?? null,
            'language' => $this->mapLanguage($languages[0] ?? null),
            'cover_id' => $coverId,
            'cover_url' => $coverId !== null
                ? "https://covers.openlibrary.org/b/id/{$coverId}-M.jpg"
                : null,
            'cover_large_url' => $coverId !== null
                ? "https://covers.openlibrary.org/b/id/{$coverId}-L.jpg"
                : null,
        ];
    }

    /**
     * Petakan kode MARC 3-huruf OpenLibrary ke kode bahasa aplikasi
     * (config/bookshelf.php). Tak dikenal -> null (form tidak diubah).
     */
    private function mapLanguage(mixed $code): ?string
    {
        if (! is_string($code)) {
            return null;
        }

        $mapped = match (strtolower($code)) {
            'ind' => 'id',
            'eng' => 'en',
            'msa', 'may' => 'ms',
            'jav' => 'jv',
            'sun' => 'su',
            'ara' => 'ar',
            'zho', 'chi' => 'zh',
            'jpn' => 'ja',
            'kor' => 'ko',
            'hin' => 'hi',
            'tha' => 'th',
            'vie' => 'vi',
            'dut', 'nld' => 'nl',
            'ger', 'deu' => 'de',
            'fre', 'fra' => 'fr',
            'spa' => 'es',
            'ita' => 'it',
            'por' => 'pt',
            'rus' => 'ru',
            'tur' => 'tr',
            'per', 'fas' => 'fa',
            'swe' => 'sv',
            default => null,
        };

        if ($mapped !== null) {
            return $mapped;
        }

        // Sudah kode 2-huruf yang didukung aplikasi? Pakai langsung.
        $short = strtolower(substr($code, 0, 2));

        return array_key_exists($short, config('bookshelf.languages', []))
            ? $short
            : null;
    }
}
