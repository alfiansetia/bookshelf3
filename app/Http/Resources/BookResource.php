<?php

namespace App\Http\Resources;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Bentuk data buku yang dikirim ke halaman Inertia.
 *
 * @mixin Book
 */
class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = $request->user();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'published_year' => $this->published_year,
            'language' => $this->language,
            'language_label' => $this->language_label,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'rating' => $this->rating,
            'synopsis' => $this->synopsis,
            'review' => $this->review,
            'cover_url' => $this->cover_url,
            'total_pages' => $this->total_pages,
            'started_at' => $this->started_at?->toDateString(),
            'finished_at' => $this->finished_at?->toDateString(),
            'is_favorite' => $this->is_favorite,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'public_url' => $this->publicUrl($request),
            'owner' => $this->whenLoaded('user', fn (): array => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'username' => $this->user->username,
                'avatar_url' => $this->user->avatar_url,
            ]),
            'can' => [
                'update' => $viewer?->can('update', $this->resource) === true,
                'delete' => $viewer?->can('delete', $this->resource) === true,
            ],
        ];
    }

    /**
     * Bentuk array buku tanpa perlu membuat instance resource di controller.
     *
     * @return array<string, mixed>
     */
    public static function forShelf(Book $book): array
    {
        return (new self($book))->resolve();
    }

    /**
     * Alamat publik buku bila pemiliknya sudah punya rak publik.
     */
    private function publicUrl(Request $request): ?string
    {
        $owner = $this->relationLoaded('user') ? $this->user : $request->user();

        if ($owner === null || $owner->id !== $this->user_id || ! $owner->hasUsername()) {
            return null;
        }

        return route('shelf.book.show', [
            'username' => $owner->username,
            'book' => $this->slug,
        ]);
    }
}
