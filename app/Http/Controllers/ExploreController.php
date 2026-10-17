<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\ShelfTheme;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExploreController extends Controller
{
    /**
     * Jelajahi rak publik milik pengguna lain.
     */
    public function __invoke(Request $request): Response
    {
        $search = $request->query('search');
        $search = is_string($search) && trim($search) !== '' ? trim($search) : null;
        $sort = $request->query('sort');

        $users = User::query()
            ->publicShelf()
            ->withCount([
                'books',
                'books as finished_count' => fn ($query) => $query->where('status', 'finished'),
            ])
            ->when($search !== null, function ($query) use ($search): void {
                $like = '%'.$search.'%';

                $query->where(function ($query) use ($like): void {
                    $query->where('name', 'like', $like)
                        ->orWhere('username', 'like', $like)
                        ->orWhere('tagline', 'like', $like);
                });
            })
            ->when($sort === 'newest', fn ($query) => $query->latest('id'))
            ->when($sort === 'name', fn ($query) => $query->orderBy('name'))
            ->when(! in_array($sort, ['newest', 'name'], true), fn ($query) => $query->orderByDesc('books_count'))
            ->paginate((int) config('bookshelf.pagination.explore'))
            ->withQueryString()
            ->through(fn (User $user): array => [
                'name' => $user->name,
                'username' => $user->username,
                'avatar_url' => $user->avatar_url,
                'tagline' => $user->tagline,
                'theme' => $user->shelfTheme()->value,
                'theme_label' => $user->shelfTheme()->label(),
                'books_count' => (int) $user->books_count,
                'finished_count' => (int) $user->finished_count,
            ]);

        return Inertia::render('Explore', [
            'shelves' => $users,
            'filters' => [
                'search' => $search,
                'sort' => in_array($sort, ['newest', 'name', 'popular'], true) ? $sort : 'popular',
            ],
            'sortOptions' => [
                ['value' => 'popular', 'label' => 'Rak paling ramai'],
                ['value' => 'newest', 'label' => 'Terbaru bergabung'],
                ['value' => 'name', 'label' => 'Nama A-Z'],
            ],
            'themes' => ShelfTheme::options(),
            'canonical' => route('explore'),
            'numbers' => [
                'readers' => User::query()->publicShelf()->count(),
                'books' => Book::query()->count(),
                'finished' => Book::query()->where('status', 'finished')->count(),
            ],
        ]);
    }
}
