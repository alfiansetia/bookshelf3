<?php

use App\Models\Book;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('menampilkan rak publik kepada pengunjung', function () {
    $owner = User::factory()->create(['username' => 'pembaca-hebat']);
    Book::factory()->for($owner)->finished()->create(['title' => 'Bumi Manusia']);
    Book::factory()->for($owner)->reading()->create();

    $this->get('/pembaca-hebat')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Profile')
            ->where('profile.username', 'pembaca-hebat')
            ->where('isOwner', false)
            ->where('stats.total', 2)
            ->has('books', 2),
        );
});

it('memberi tahu pengunjung saat rak bersifat privat', function () {
    $owner = User::factory()->privateShelf()->create(['username' => 'pembaca-privat']);
    Book::factory()->for($owner)->create();

    $this->get('/pembaca-privat')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Private')
            ->where('username', 'pembaca-privat')
            ->where('context', 'profile')
            ->missing('books')
            ->missing('stats'),
        );

    $this->actingAs($owner)
        ->get('/pembaca-privat')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Profile')
            ->where('isOwner', true),
        );
});

it('memberi tahu pengunjung saat buku ada di rak privat', function () {
    $owner = User::factory()->privateShelf()->create(['username' => 'rak-rahasia']);
    $book = Book::factory()->for($owner)->create();

    $this->get("/rak-rahasia/{$book->slug}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Private')
            ->where('username', 'rak-rahasia')
            ->where('context', 'book')
            ->missing('book'),
        );
});

it('mengembalikan 404 untuk username yang tidak ada', function () {
    $this->get('/tidak-ada-orang-ini')->assertNotFound();
});

it('tidak membocorkan buku milik pengguna lain di rak publik', function () {
    $owner = User::factory()->create(['username' => 'rak-sendiri']);
    $other = User::factory()->create();

    Book::factory()->for($owner)->finished()->create(['title' => 'Buku Sendiri']);
    Book::factory()->for($other)->finished()->create(['title' => 'Buku Orang Lain']);

    $this->get('/rak-sendiri')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.total', 1)
            ->where('books.0.title', 'Buku Sendiri'),
        );
});

it('membuka detail buku dari rak publik', function () {
    $owner = User::factory()->create(['username' => 'pemilik-rak']);
    $book = Book::factory()->for($owner)->finished()->create([
        'title' => 'Bumi Manusia',
    ]);

    $this->get("/pemilik-rak/{$book->slug}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Book')
            ->where('book.title', 'Bumi Manusia')
            ->where('profile.username', 'pemilik-rak'),
        );
});

it('tetap mendaftarkan route aplikasi sebelum rak publik', function () {
    $this->get('/login')->assertOk();
    $this->get('/register')->assertOk();
    $this->get('/jelajah')->assertOk();
});

it('menampilkan tema rak sesuai pilihan pemiliknya', function (string $theme) {
    $owner = User::factory()->create([
        'username' => 'rak-'.$theme,
        'theme' => $theme,
    ]);

    Book::factory()->for($owner)->finished()->create();

    $this->get('/rak-'.$theme)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Profile')
            ->where('profile.theme', $theme),
        );
})->with(['wood', 'minimal', 'neon', 'cozy', 'story', 'catalog', 'stack']);
