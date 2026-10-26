<?php

namespace App\Http\Controllers\Shelf;

use App\BookStatus;
use App\Concerns\FiltersShelfBooks;
use App\Concerns\RendersBlockedShelf;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShelfProfileResource;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicProfileController extends Controller
{
    use FiltersShelfBooks;
    use RendersBlockedShelf;

    /**
     * Rak publik milik seorang pengguna.
     *
     * Seluruh buku dikirim sebagai kartu ramping; pencarian, bahasa,
     * favorit, urutan, dan spotlight status dikerjakan di frontend
     * supaya interaksi instan tanpa roundtrip server.
     */
    public function __invoke(Request $request, string $username): Response
    {
        $user = User::query()->whereUsername($username)->firstOrFail();
        $viewer = $request->user();
        $isOwner = $viewer !== null && $viewer->id === $user->id;

        if ($user->isBlocked()) {
            return $this->blockedShelf($user, 'profile');
        }

        if (! $user->is_public && ! $isOwner) {
            return Inertia::render('public/Private', [
                'username' => $user->username,
                'context' => 'profile',
            ]);
        }

        $base = Book::query()->whereBelongsTo($user);

        $books = (clone $base)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('public/Profile', [
            'profile' => (new ShelfProfileResource($user))->resolve(),
            'stats' => $this->shelfSummary(clone $base),
            'books' => $this->shelfBooks($books),
            'statusCounts' => $this->bookStatusCounts(clone $base),
            'languages' => $this->bookLanguagesInShelf(clone $base),
            'statuses' => BookStatus::options(),
            'sortOptions' => Book::sortOptions(),
            'isOwner' => $isOwner,
            'canonical' => route('shelf.show', $user->username),
        ]);
    }
}
