<?php

use App\Models\Book;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('menampilkan halaman galat aplikasi untuk alamat yang tidak dikenal', function () {
    config(['app.debug' => false]);

    $this->get('/alamat/yang/tidak/pernah/ada')
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page
            ->component('errors/Error')
            ->where('status', 404)
            ->where('auth.user', null),
        );
});

it('menampilkan halaman galat aplikasi saat username rak tidak ada', function () {
    config(['app.debug' => false]);

    $this->get('/tidak-ada-orang-ini')
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page
            ->component('errors/Error')
            ->where('status', 404),
        );
});

it('menampilkan halaman galat aplikasi saat akses ditolak', function () {
    config(['app.debug' => false]);

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
        ->assertForbidden()
        ->assertInertia(fn (Assert $page) => $page
            ->component('errors/Error')
            ->where('status', 403)
            ->where('auth.user.id', $intruder->id),
        );
});

it('memakai halaman debug bawaan Laravel untuk galat server saat debug aktif', function () {
    config(['app.debug' => true]);

    $this->get('/alamat/yang/tidak/pernah/ada')->assertNotFound();
});

it('tidak mengubah status galat yang tidak ditangani', function () {
    config(['app.debug' => false]);

    $this->post(route('home'))->assertMethodNotAllowed();
});
