<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\User;
use App\Services\ProfanityScanner;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfanityScanController extends Controller
{
    public function __construct(private ProfanityScanner $scanner) {}

    /**
     * Pindai seluruh data tersimpan dan tampilkan yang mengandung kata kasar.
     */
    public function __invoke(Request $request): Response
    {
        $users = User::query()
            ->select([
                'id', 'name', 'username', 'email', 'tagline', 'bio',
                'social_instagram', 'social_tiktok', 'social_x',
                'social_youtube', 'social_linkedin', 'social_website',
                'blocked_at',
            ])
            ->orderBy('id')
            ->limit(2000)
            ->get();

        $books = Book::query()
            ->with('user:id,name,username')
            ->select([
                'id', 'user_id', 'title', 'slug', 'author', 'publisher',
                'synopsis', 'review',
            ])
            ->orderByDesc('id')
            ->limit(5000)
            ->get();

        $userFindings = [];

        foreach ($users as $user) {
            foreach ($this->userFields($user) as $field => $value) {
                $matched = $field === 'username' || str_starts_with($field, 'social_')
                    ? $this->scanner->findInHandle($value)
                    : $this->scanner->findInText($value);

                if ($matched === []) {
                    continue;
                }

                $userFindings[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'is_blocked' => $user->blocked_at !== null,
                    'field' => $field,
                    'field_label' => $this->fieldLabel($field),
                    'snippet' => $this->scanner->snippet($value),
                    'matched' => $matched,
                    'shelf_url' => filled($user->username) ? route('shelf.show', $user->username) : null,
                ];
            }
        }

        $bookFindings = [];

        foreach ($books as $book) {
            foreach ($this->bookFields($book) as $field => $value) {
                $matched = $this->scanner->findInText($value);

                if ($matched === []) {
                    continue;
                }

                $bookFindings[] = [
                    'id' => $book->id,
                    'title' => $book->title,
                    'field' => $field,
                    'field_label' => $this->fieldLabel($field),
                    'snippet' => $this->scanner->snippet($value),
                    'matched' => $matched,
                    'owner_name' => $book->user->name,
                    'owner_username' => $book->user->username,
                    'shelf_url' => filled($book->user->username) ? route('shelf.book.show', [$book->user->username, $book->slug]) : null,
                ];
            }
        }

        // Batasi baris yang dikirim ke frontend agar halaman tetap ringan.
        $userFindings = array_slice($userFindings, 0, 200);
        $bookFindings = array_slice($bookFindings, 0, 200);

        return Inertia::render('admin/Profanity', [
            'summary' => [
                'users_scanned' => $users->count(),
                'books_scanned' => $books->count(),
                'user_hits' => count($userFindings),
                'book_hits' => count($bookFindings),
                'words_count' => count($this->scanner->words()),
            ],
            'userFindings' => $userFindings,
            'bookFindings' => $bookFindings,
            'scannedAt' => now()->toIso8601String(),
        ]);
    }

    /**
     * Kolom pengguna yang ikut dipindai.
     *
     * @return array<string, ?string>
     */
    private function userFields(User $user): array
    {
        return [
            'name' => $user->name,
            'username' => $user->username,
            'tagline' => $user->tagline,
            'bio' => $user->bio,
            'social_instagram' => $user->social_instagram,
            'social_tiktok' => $user->social_tiktok,
            'social_x' => $user->social_x,
            'social_youtube' => $user->social_youtube,
            'social_linkedin' => $user->social_linkedin,
        ];
    }

    /**
     * Kolom buku yang ikut dipindai.
     *
     * @return array<string, ?string>
     */
    private function bookFields(Book $book): array
    {
        return [
            'synopsis' => $book->synopsis,
            'review' => $book->review,
        ];
    }

    /**
     * Label Bahasa Indonesia untuk nama kolom.
     */
    private function fieldLabel(string $field): string
    {
        return match ($field) {
            'name' => 'Nama',
            'username' => 'Username',
            'tagline' => 'Tagline',
            'bio' => 'Bio',
            'title' => 'Judul',
            'author' => 'Penulis',
            'publisher' => 'Penerbit',
            'synopsis' => 'Sinopsis',
            'review' => 'Review',
            'social_instagram' => 'Instagram',
            'social_tiktok' => 'TikTok',
            'social_x' => 'X',
            'social_youtube' => 'YouTube',
            'social_linkedin' => 'LinkedIn',
            default => $field,
        };
    }
}
