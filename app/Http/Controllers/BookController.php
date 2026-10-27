<?php

namespace App\Http\Controllers;

use App\Actions\StoreUploadedImage;
use App\BookStatus;
use App\Concerns\FiltersShelfBooks;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\User;
use App\UploadedImageType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    use FiltersShelfBooks;

    /**
     * Host CDN katalog yang boleh dijadikan sumber sampul via cover_url.
     *
     * @var list<string>
     */
    private const COVER_URL_HOSTS = [
        'covers.openlibrary.org',
        'books.google.com',
        'books.google.co.id',
    ];

    /** Batas isi unduhan sampul (sampul katalog umumnya puluhan KB). */
    private const COVER_DOWNLOAD_MAX_BYTES = 8 * 1024 * 1024;

    public function __construct(private StoreUploadedImage $imageStore) {}

    /**
     * Daftar buku milik pengguna beserta filternya.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $filters = $this->shelfFiltersFrom($request);
        $base = Book::query()->whereBelongsTo($user);

        $books = $this->applyShelfFilters(clone $base, $filters)
            ->paginate((int) config('bookshelf.pagination.books'))
            ->withQueryString()
            ->through(fn (Book $book): array => BookResource::forShelf($book));

        return Inertia::render('books/Index', [
            'books' => $books,
            'filters' => $filters,
            'statuses' => BookStatus::options(),
            'languages' => $this->bookLanguagesInShelf(clone $base),
            'languageOptions' => Book::languageOptions(),
            'sortOptions' => Book::sortOptions(),
            'statusCounts' => $this->bookStatusCounts(clone $base),
            'stats' => $this->shelfSummary(clone $base),
            'shelfUrl' => $user->hasUsername() ? route('shelf.show', $user->username) : null,
        ]);
    }

    /**
     * Form tambah buku.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('books/Create', $this->formProps($request->user()));
    }

    /**
     * Simpan buku baru ke rak pengguna.
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->books()->count() >= (int) config('bookshelf.limits.books_per_user')) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Rak kamu sudah penuh. Hapus beberapa buku sebelum menambah yang baru.',
            ]);

            return to_route('books.index');
        }

        $book = $user->books()->create([
            ...$request->safe()->except(['cover', 'cover_url', 'remove_cover']),
            'cover_path' => $this->storeCover($request),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => '"'.$book->title.'" sudah masuk rak kamu.',
        ]);

        return to_route('books.index');
    }

    /**
     * Form ubah buku.
     */
    public function edit(Request $request, Book $book): Response
    {
        Gate::authorize('update', $book);

        return Inertia::render('books/Edit', [
            ...$this->formProps($request->user()),
            'book' => BookResource::forShelf($book),
        ]);
    }

    /**
     * Perbarui data buku.
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $attributes = $request->safe()->except(['cover', 'cover_url', 'remove_cover']);
        $newCover = $this->storeCover($request);

        if ($newCover !== null) {
            $this->deleteCover($book->cover_path);
            $attributes['cover_path'] = $newCover;
        } elseif ($request->boolean('remove_cover')) {
            $this->deleteCover($book->cover_path);
            $attributes['cover_path'] = null;
        }

        $book->update($attributes);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan pada "'.$book->title.'" sudah disimpan.',
        ]);

        return to_route('books.index');
    }

    /**
     * Hapus buku dari rak.
     */
    public function destroy(Book $book): RedirectResponse
    {
        Gate::authorize('delete', $book);

        $this->deleteCover($book->cover_path);

        $title = $book->title;
        $book->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => '"'.$title.'" sudah dihapus dari rak.',
        ]);

        return to_route('books.index');
    }

    /**
     * Prop bersama untuk form tambah dan ubah buku.
     *
     * @return array<string, mixed>
     */
    private function formProps(?User $user): array
    {
        return [
            'statuses' => BookStatus::options(),
            'languages' => Book::languageOptions(),
            'coverMaxKilobytes' => (int) config('bookshelf.cover.max_kilobytes'),
            'shelfUrl' => $user?->hasUsername() ? route('shelf.show', $user->username) : null,
            'lookupUrl' => route('books.lookup'),
            'book' => null,
        ];
    }

    /**
     * Simpan sampul yang diunggah, unduhan dari URL pilihan lookup,
     * atau null bila tidak ada. URL hanya diterima dari host CDN
     * katalog buku yang dikenal (anti-SSRF).
     */
    private function storeCover(Request $request): ?string
    {
        if ($request->hasFile('cover')) {
            return $this->imageStore->store($request->file('cover'), UploadedImageType::Cover);
        }

        $url = is_string($request->input('cover_url')) ? trim($request->input('cover_url')) : '';

        if ($url === '' || ! in_array(mb_strtolower((string) parse_url($url, PHP_URL_HOST)), self::COVER_URL_HOSTS, true)) {
            return null;
        }

        try {
            $response = Http::timeout(12)->withUserAgent('Bookshelf3/1.0')->get($url);

            if (! $response->ok()) {
                return null;
            }

            $contentType = mb_strtolower($response->header('Content-Type'));

            if (! str_starts_with($contentType, 'image/')) {
                return null;
            }

            $body = $response->body();

            if ($body === '' || strlen($body) > self::COVER_DOWNLOAD_MAX_BYTES) {
                return null;
            }

            return $this->imageStore->storeContents($body, UploadedImageType::Cover);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Hapus berkas sampul lama.
     */
    private function deleteCover(?string $path): void
    {
        $this->imageStore->delete($path, UploadedImageType::Cover);
    }
}
