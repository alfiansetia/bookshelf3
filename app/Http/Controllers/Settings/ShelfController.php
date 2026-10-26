<?php

namespace App\Http\Controllers\Settings;

use App\Concerns\FiltersShelfBooks;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateShelfPreferencesRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\ShelfTheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShelfController extends Controller
{
    use FiltersShelfBooks;

    /**
     * Halaman pemilihan gaya rak dan visibilitas.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $base = Book::query()->whereBelongsTo($user);

        $previewBooks = (clone $base)
            ->orderByDesc('cover_path')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return Inertia::render('settings/Shelf', [
            'themes' => ShelfTheme::options(),
            'selectedTheme' => $user->shelfTheme()->value,
            'isPublic' => $user->is_public,
            'shelfUrl' => $user->hasUsername() ? route('shelf.show', $user->username) : null,
            'username' => $user->username,
            'stats' => $this->shelfSummary(clone $base),
            'previewBooks' => $previewBooks->map(fn (Book $book): array => BookResource::forShelfCard($book))->all(),
        ]);
    }

    /**
     * Simpan gaya rak dan visibilitas.
     */
    public function update(UpdateShelfPreferencesRequest $request): RedirectResponse
    {
        $request->user()->forceFill($request->validated())->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Gaya rak kamu sudah diperbarui.',
        ]);

        return to_route('shelf.edit');
    }
}
