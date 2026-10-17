<?php

namespace App\Http\Controllers;

use App\BookStatus;
use App\Concerns\FiltersShelfBooks;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use FiltersShelfBooks;

    /**
     * Ringkasan rak pengguna yang sedang login.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $base = Book::query()->whereBelongsTo($user);

        $currentlyReading = (clone $base)
            ->withStatus(BookStatus::Reading)
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $recentlyAdded = (clone $base)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $favorites = (clone $base)
            ->favorites()
            ->orderByDesc('rating')
            ->limit(4)
            ->get();

        $topRated = (clone $base)
            ->whereNotNull('rating')
            ->orderByDesc('rating')
            ->orderByDesc('finished_at')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                ...$this->shelfSummary(clone $base),
                'languages' => count($this->bookLanguagesInShelf(clone $base)),
            ],
            'statuses' => BookStatus::options(),
            'statusCounts' => $this->bookStatusCounts(clone $base),
            'currentlyReading' => $currentlyReading->map(fn (Book $book): array => BookResource::forShelf($book))->all(),
            'recentlyAdded' => $recentlyAdded->map(fn (Book $book): array => BookResource::forShelf($book))->all(),
            'favorites' => $favorites->map(fn (Book $book): array => BookResource::forShelf($book))->all(),
            'topRated' => $topRated->map(fn (Book $book): array => BookResource::forShelf($book))->all(),
            'languages' => $this->bookLanguagesInShelf(clone $base),
            'shelfUrl' => $user->hasUsername() ? route('shelf.show', $user->username) : null,
            'share' => [
                'url' => $user->hasUsername() ? route('shelf.show', $user->username) : null,
                'public' => $user->shelfIsPublic(),
            ],
        ]);
    }
}
