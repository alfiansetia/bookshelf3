<?php

use App\Models\Book;
use App\Models\User;

it('menampilkan temuan kata kasar untuk admin', function () {
    $admin = User::factory()->admin()->create();
    $badUser = User::factory()->create(['bio' => 'Kamu itu bangsat ya']);
    Book::factory()->for($badUser)->create([
        'title' => 'Buku Bersih',
        'author' => 'Penulis Baik',
        'publisher' => 'Penerbit Baik',
        'synopsis' => 'Sinopsis yang aman untuk semua umur.',
        'review' => 'Buku ini goblok sekali',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.profanity.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/Profanity')
            ->where('summary.user_hits', 1)
            ->has('userFindings', 1)
            ->has('bookFindings', 1)
        );
});

it('menampilkan halaman bersih bila tidak ada kata kasar', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['bio' => 'Pembaca santai dari Bandung']);
    Book::factory()->for($user)->create([
        'title' => 'Buku Bersih',
        'author' => 'Penulis Baik',
        'publisher' => 'Penerbit Baik',
        'synopsis' => 'Sinopsis yang aman untuk semua umur.',
        'review' => 'Buku yang sangat bagus.',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.profanity.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/Profanity')
            ->where('summary.user_hits', 0)
            ->where('summary.book_hits', 0)
        );
});

it('menolak pengguna non-admin membuka halaman scan', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.profanity.index'))
        ->assertRedirect(route('dashboard'));
});
