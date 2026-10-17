<?php

namespace App\Http\Controllers;

use App\BookStatus;
use App\Models\Book;
use App\Models\User;
use App\ShelfTheme;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    /**
     * Halaman depan yang menjelaskan produk dan mengajak mendaftar.
     */
    public function __invoke(Request $request): Response
    {
        $featuredShelves = User::query()
            ->publicShelf()
            ->withCount('books')
            ->whereHas('books')
            ->orderByDesc('books_count')
            ->limit(3)
            ->get()
            ->map(fn ($user): array => [
                'name' => $user->name,
                'username' => $user->username,
                'avatar_url' => $user->avatar_url,
                'tagline' => $user->tagline,
                'theme' => $user->shelfTheme()->value,
                'theme_label' => $user->shelfTheme()->label(),
                'books_count' => (int) $user->books_count,
                'covers' => Book::query()
                    ->whereBelongsTo($user)
                    ->whereNotNull('cover_path')
                    ->limit(4)
                    ->get()
                    ->map(fn (Book $book): string => (string) $book->cover_url)
                    ->all(),
            ]);

        return Inertia::render('Welcome', [
            'themes' => ShelfTheme::options(),
            'statuses' => BookStatus::options(),
            'featuredShelves' => $featuredShelves,
            'canonical' => route('home'),
            'numbers' => [
                'readers' => User::query()->publicShelf()->count(),
                'books' => Book::query()->count(),
                'finished' => Book::query()->where('status', BookStatus::Finished->value)->count(),
            ],
        ]);
    }
}
