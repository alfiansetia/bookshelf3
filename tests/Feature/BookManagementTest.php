<?php

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

it('mengalihkan pengguna tanpa username ke onboarding', function () {
    $user = User::factory()->withoutUsername()->create();

    $this->actingAs($user)
        ->get(route('books.index'))
        ->assertRedirect(route('onboarding.username.edit'));

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('onboarding.username.edit'));
});

it('menyimpan buku baru beserta slug-nya', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('books.store'), [
            'title' => 'Bumi Manusia',
            'author' => 'Pramoedya Ananta Toer',
            'publisher' => 'Hasta Mitra',
            'published_year' => 1980,
            'language' => 'id',
            'status' => 'finished',
            'rating' => 5,
            'synopsis' => 'Kisah Minke pada masa kolonial.',
            'review' => 'Bacaan wajib.',
            'total_pages' => 535,
            'is_favorite' => true,
        ])
        ->assertRedirect(route('books.index'));

    $book = Book::query()->whereBelongsTo($user)->sole();

    expect($book->title)->toBe('Bumi Manusia')
        ->and($book->slug)->toBe('bumi-manusia')
        ->and($book->language)->toBe('id')
        ->and($book->status->value)->toBe('finished')
        ->and($book->rating)->toBe(5)
        ->and($book->is_favorite)->toBeTrue();
});

it('menolak buku tanpa judul dan penulis', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('books.create'))
        ->post(route('books.store'), [
            'language' => 'id',
            'status' => 'reading',
        ])
        ->assertSessionHasErrors(['title', 'author'])
        ->assertRedirect(route('books.create'));

    $this->assertDatabaseCount('books', 0);
});

it('menolak bahasa yang tidak didukung', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('books.store'), [
            'title' => 'Buku Aneh',
            'author' => 'Penulis',
            'language' => 'klingon',
            'status' => 'reading',
        ])
        ->assertSessionHasErrors('language');

    $this->assertDatabaseCount('books', 0);
});

it('menyimpan sampul yang diunggah', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('books.store'), [
            'title' => 'Buku Bersampul',
            'author' => 'Penulis',
            'language' => 'id',
            'status' => 'reading',
            'cover' => UploadedFile::fake()->image('sampul.jpg', 300, 450),
        ])
        ->assertRedirect(route('books.index'));

    $book = Book::query()->whereBelongsTo($user)->sole();

    expect($book->cover_path)->not->toBeNull();
    Storage::disk('public')->assertExists($book->cover_path);
});

it('memperbarui buku milik sendiri', function () {
    $user = User::factory()->create();
    $book = Book::factory()->for($user)->create(['title' => 'Judul Lama']);

    $this->actingAs($user)
        ->put(route('books.update', $book), [
            'title' => 'Judul Baru',
            'author' => $book->author,
            'language' => $book->language,
            'status' => 'finished',
            'rating' => 4,
        ])
        ->assertRedirect(route('books.index'));

    $book->refresh();

    expect($book->title)->toBe('Judul Baru')
        ->and($book->slug)->toBe('judul-baru')
        ->and($book->status->value)->toBe('finished')
        ->and($book->rating)->toBe(4);
});

it('melarang pengguna mengubah atau menghapus buku milik orang lain', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $book = Book::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->put(route('books.update', $book), [
            'title' => 'Dibajak',
            'author' => 'Penyerobot',
            'language' => 'id',
            'status' => 'reading',
        ])
        ->assertForbidden();

    $this->actingAs($intruder)
        ->delete(route('books.destroy', $book))
        ->assertForbidden();

    expect($book->fresh())->not->toBeNull();
});

it('menyimpan warna sampul pilihan pemilik', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('books.store'), [
            'title' => 'Buku Warna',
            'author' => 'Penulis',
            'language' => 'id',
            'status' => 'reading',
            'cover_color' => '#1d4ed8',
        ])
        ->assertRedirect(route('books.index'));

    $book = Book::query()->whereBelongsTo($user)->sole();

    expect($book->cover_color)->toBe('#1d4ed8');
});

it('menolak warna sampul yang bukan hex', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('books.store'), [
            'title' => 'Buku Warna',
            'author' => 'Penulis',
            'language' => 'id',
            'status' => 'reading',
            'cover_color' => 'biru',
        ])
        ->assertSessionHasErrors('cover_color');

    $this->assertDatabaseCount('books', 0);
});

it('menghapus buku milik sendiri', function () {
    $user = User::factory()->create();
    $book = Book::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('books.destroy', $book))
        ->assertRedirect(route('books.index'));

    $this->assertDatabaseMissing('books', ['id' => $book->id]);
});

it('menyaring buku berdasarkan bahasa dan status', function () {
    $user = User::factory()->create();

    Book::factory()->for($user)->create([
        'title' => 'Buku Indonesia',
        'language' => 'id',
        'status' => 'finished',
    ]);

    Book::factory()->for($user)->create([
        'title' => 'English Book',
        'language' => 'en',
        'status' => 'reading',
    ]);

    $this->actingAs($user)
        ->get(route('books.index', ['language' => 'id', 'status' => 'finished']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('books/Index')
            ->has('books.data', 1)
            ->where('books.data.0.title', 'Buku Indonesia')
            ->where('filters.language', 'id')
            ->where('filters.status', 'finished'),
        );
});
