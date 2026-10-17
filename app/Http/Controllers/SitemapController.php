<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Daftar URL publik untuk mesin pencari.
     *
     * Hanya rak publik yang disertakan; rak privat beserta bukunya tidak
     * pernah masuk sitemap.
     */
    public function __invoke(): Response
    {
        $users = User::query()
            ->publicShelf()
            ->whereNotNull('username')
            ->select(['id', 'username', 'updated_at'])
            ->get();

        $books = Book::query()
            ->whereIn('user_id', $users->pluck('id'))
            ->select(['user_id', 'slug', 'updated_at'])
            ->orderByDesc('updated_at')
            ->limit((int) config('bookshelf.seo.sitemap_book_limit'))
            ->get();

        $usernames = $users->pluck('username', 'id');

        $urls = [
            [
                'loc' => route('home'),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            [
                'loc' => route('explore'),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'loc' => route('legal.cookie'),
                'changefreq' => 'yearly',
                'priority' => '0.2',
            ],
        ];

        foreach ($users as $user) {
            $urls[] = [
                'loc' => route('shelf.show', $user->username),
                'lastmod' => $user->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        foreach ($books as $book) {
            $username = $usernames[$book->user_id] ?? null;

            if ($username === null) {
                continue;
            }

            $urls[] = [
                'loc' => route('shelf.book.show', [
                    'username' => $username,
                    'book' => $book->slug,
                ]),
                'lastmod' => $book->updated_at?->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        return response(
            $this->toXml($urls),
            200,
            ['Content-Type' => 'application/xml; charset=UTF-8'],
        );
    }

    /**
     * Susun dokumen sitemap dari daftar URL.
     *
     * @param  array<int, array{loc: string, lastmod?: ?string, changefreq?: string, priority?: string}>  $urls
     */
    private function toXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "    <url>\n";
            $xml .= '        <loc>'.e($url['loc'])."</loc>\n";

            if (! empty($url['lastmod'])) {
                $xml .= '        <lastmod>'.e($url['lastmod'])."</lastmod>\n";
            }

            if (! empty($url['changefreq'])) {
                $xml .= '        <changefreq>'.$url['changefreq']."</changefreq>\n";
            }

            if (! empty($url['priority'])) {
                $xml .= '        <priority>'.$url['priority']."</priority>\n";
            }

            $xml .= "    </url>\n";
        }

        return $xml.'</urlset>'."\n";
    }
}
