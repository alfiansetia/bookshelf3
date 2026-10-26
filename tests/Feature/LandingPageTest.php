<?php

use App\Models\Book;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

it('menampilkan halaman depan beserta rak unggulan', function () {
    Storage::fake('public');

    $owner = User::factory()->create(['username' => 'rak-hangat']);

    Book::factory()->for($owner)->create([
        'title' => 'Bumi Manusia',
        'cover_path' => 'covers/demo/bumi-manusia.svg',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->has('themes', 6)
            ->has('statuses', 5)
            ->where('numbers.readers', 1)
            ->has('featuredShelves', 1)
            ->where('featuredShelves.0.username', 'rak-hangat')
            ->where(
                'featuredShelves.0.covers.0',
                Storage::disk('public')->url('covers/demo/bumi-manusia.svg'),
            ),
        );
});

it('menyembunyikan rak privat dari halaman depan', function () {
    $owner = User::factory()->privateShelf()->create(['username' => 'rak-privat']);
    Book::factory()->for($owner)->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('numbers.readers', 0)
            ->has('featuredShelves', 0),
        );
});

it('menampilkan halaman jelajah dengan rak publik', function () {
    $owner = User::factory()->create(['username' => 'rak-jelajah']);
    Book::factory()->for($owner)->finished()->create();

    $this->get(route('explore'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Explore')
            ->has('shelves.data', 1)
            ->where('shelves.data.0.username', 'rak-jelajah')
            ->where('shelves.data.0.finished_count', 1),
        );
});
