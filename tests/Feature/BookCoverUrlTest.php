<?php

use App\Models\Book;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

// PNG 1x1 valid untuk pipeline image (orient -> scale -> optimize).
const COVER_PNG_1X1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

it('mengunduh sampul dari url lookup saat menyimpan buku', function () {
    Storage::fake('public');

    Http::fake([
        'covers.openlibrary.org/*' => Http::response(
            base64_decode(COVER_PNG_1X1),
            200,
            ['Content-Type' => 'image/png'],
        ),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('books.store'), [
            'title' => 'Buku Bersampul Lookup',
            'author' => 'Penulis',
            'language' => 'id',
            'status' => 'reading',
            'cover_url' => 'https://covers.openlibrary.org/b/id/123-M.jpg',
        ])
        ->assertRedirect(route('books.index'));

    $book = Book::query()->whereBelongsTo($user)->sole();

    expect($book->cover_path)->not->toBeNull();
    Storage::disk('public')->assertExists($book->cover_path);
});

it('mengabaikan cover_url dari host yang tidak dikenal', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('books.store'), [
            'title' => 'Buku Tanpa Sampul Asing',
            'author' => 'Penulis',
            'language' => 'id',
            'status' => 'reading',
            'cover_url' => 'https://evil.test/sampul.jpg',
        ])
        ->assertRedirect(route('books.index'));

    $book = Book::query()->whereBelongsTo($user)->sole();

    expect($book->cover_path)->toBeNull();
});

it('mengutamakan berkas sampul manual dibanding cover_url', function () {
    Storage::fake('public');

    Http::fake([
        'covers.openlibrary.org/*' => function () {
            throw new RuntimeException('Backend tidak boleh mengunduh saat ada berkas manual');
        },
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('books.store'), [
            'title' => 'Buku Sampul Manual',
            'author' => 'Penulis',
            'language' => 'id',
            'status' => 'reading',
            'cover' => Illuminate\Http\UploadedFile::fake()->image('sampul.jpg', 300, 450),
            'cover_url' => 'https://covers.openlibrary.org/b/id/123-M.jpg',
        ])
        ->assertRedirect(route('books.index'));

    $book = Book::query()->whereBelongsTo($user)->sole();

    expect($book->cover_path)->not->toBeNull();
    Storage::disk('public')->assertExists($book->cover_path);
});
