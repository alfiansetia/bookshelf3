<?php

namespace App\Services;

use App\Models\BookLookupCache;

class BookMetadataLookup
{
    private const TTL_DAYS = 30;

    public function __construct(
        private GoogleBooksBookLookup $google,
        private OpenLibraryBookLookup $openLibrary,
    ) {}

    /**
     * Cari metadata buku. Urutan: DB dulu, lalu Google Books,
     * lalu OpenLibrary sebagai fallback. Hasil API langsung
     * di-upsert ke DB untuk request berikutnya.
     *
     * @return array{data: array<int, array<string, mixed>>, source: string, provider: string}
     */
    public function search(string $query): array
    {
        $normalized = $this->normalize($query);
        $hash = md5($normalized);

        // Google dulu (datanya lebih bagus untuk buku Indonesia),
        // lalu OpenLibrary.
        foreach ([GoogleBooksBookLookup::PROVIDER, OpenLibraryBookLookup::PROVIDER] as $provider) {
            $cached = BookLookupCache::query()
                ->where('provider', $provider)
                ->where('query_hash', $hash)
                ->first();

            if ($cached !== null && $cached->isFresh()) {
                $cached->increment('hits');

                return [
                    'data' => $cached->results,
                    'source' => 'db',
                    'provider' => $provider,
                ];
            }
        }

        $results = $this->google->fetch($normalized);
        $provider = GoogleBooksBookLookup::PROVIDER;

        if ($results === []) {
            $results = $this->openLibrary->fetch($normalized);
            $provider = OpenLibraryBookLookup::PROVIDER;
        }

        $record = BookLookupCache::firstOrNew(
            ['provider' => $provider, 'query_hash' => $hash],
        );

        $record->fill([
            'query' => mb_substr($normalized, 0, 255),
            'results' => $results,
            'fetched_at' => now(),
            // Hasil kosong di-cache singkat saja supaya data baru
            // di provider bisa muncul tanpa menunggu 30 hari.
            'expires_at' => $results === []
                ? now()->addDay()
                : now()->addDays(self::TTL_DAYS),
        ]);
        $record->hits = ($record->exists ? (int) $record->hits : 0) + 1;
        $record->save();

        return ['data' => $results, 'source' => 'api', 'provider' => $provider];
    }

    private function normalize(string $query): string
    {
        $query = mb_strtolower(trim(preg_replace('/\s+/', ' ', $query) ?? ''));
        $query = (string) preg_replace('/[^\p{L}\p{N}\s\-:\']/u', '', $query);

        return mb_substr(trim($query), 0, 255);
    }
}
