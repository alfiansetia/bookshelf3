<?php

namespace App\Http\Controllers\Shelf;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Http\Resources\ShelfProfileResource;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicBookController extends Controller
{
    /**
     * Detail satu buku pada rak publik.
     */
    public function __invoke(Request $request, string $username, string $book): Response
    {
        $user = User::query()->whereUsername($username)->firstOrFail();
        $viewer = $request->user();
        $isOwner = $viewer !== null && $viewer->id === $user->id;

        if (! $user->is_public && ! $isOwner) {
            abort(404);
        }

        $model = Book::query()
            ->whereBelongsTo($user)
            ->where('slug', $book)
            ->with('user')
            ->firstOrFail();

        $related = Book::query()
            ->whereBelongsTo($user)
            ->whereKeyNot($model->getKey())
            ->inLanguage($model->language)
            ->orderByDesc('rating')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        if ($related->count() < 4) {
            $fallback = Book::query()
                ->whereBelongsTo($user)
                ->whereKeyNot($model->getKey())
                ->whereNotIn('id', $related->pluck('id'))
                ->orderByDesc('created_at')
                ->limit(4 - $related->count())
                ->get();

            $related = $related->concat($fallback);
        }

        return Inertia::render('public/Book', [
            'profile' => (new ShelfProfileResource($user))->resolve(),
            'book' => BookResource::forShelf($model),
            'related' => $related->map(fn (Book $item): array => BookResource::forShelf($item))->values()->all(),
            'isOwner' => $isOwner,
            'canonical' => route('shelf.book.show', [
                'username' => $user->username,
                'book' => $model->slug,
            ]),
        ]);
    }
}
