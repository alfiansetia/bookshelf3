<?php

namespace App\Http\Controllers\Shelf;

use App\BookStatus;
use App\Concerns\FiltersShelfBooks;
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

    /**
     * Rak publik milik seorang pengguna.
     */
    public function __invoke(Request $request, string $username): Response
    {
        $user = User::query()->whereUsername($username)->firstOrFail();
        $viewer = $request->user();
        $isOwner = $viewer !== null && $viewer->id === $user->id;

        if (! $user->is_public && ! $isOwner) {
            abort(404);
        }

        $filters = $this->shelfFiltersFrom($request);
        $base = Book::query()->whereBelongsTo($user);
        $limit = (int) config('bookshelf.pagination.shelf');

        $filteredTotal = $this->applyShelfFilters(clone $base, $filters)->count();

        $books = $this->applyShelfFilters(clone $base, $filters)
            ->with('user')
            ->limit($limit)
            ->get();

        return Inertia::render('public/Profile', [
            'profile' => (new ShelfProfileResource($user))->resolve(),
            'stats' => $this->shelfSummary(clone $base),
            'rows' => $this->shelfRows($books),
            'statusCounts' => $this->bookStatusCounts(clone $base),
            'languages' => $this->bookLanguagesInShelf(clone $base),
            'statuses' => BookStatus::options(),
            'sortOptions' => Book::sortOptions(),
            'filters' => $filters,
            'isOwner' => $isOwner,
            'limit' => $limit,
            'filteredTotal' => $filteredTotal,
            'canonical' => route('shelf.show', $user->username),
        ]);
    }
}
