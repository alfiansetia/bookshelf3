<?php

use App\Models\User;

it('menolak username yang mengandung kata kasar', function () {
    $user = User::factory()->withoutUsername()->create();

    $this->actingAs($user)
        ->put(route('onboarding.username.update'), ['username' => 'anjing123'])
        ->assertSessionHasErrors('username');

    expect($user->fresh()->username)->toBeNull();
});

it('menolak username yang menyamarkan kata kasar dengan pemisah', function () {
    $user = User::factory()->withoutUsername()->create();

    $this->actingAs($user)
        ->put(route('onboarding.username.update'), ['username' => 'an.jing-ku'])
        ->assertSessionHasErrors('username');
});

it('menolak bio dan tagline yang mengandung kata kasar', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'tagline' => 'Pembaca yang tolol',
            'bio' => 'Halo semua',
        ])
        ->assertSessionHasErrors('tagline');

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'tagline' => 'Pembaca santai',
            'bio' => 'Kamu itu bangsat ya',
        ])
        ->assertSessionHasErrors('bio');
});

it('menolak review dan sinopsis buku yang mengandung kata kasar', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('books.store'), [
            'title' => 'Buku Bersih',
            'author' => 'Penulis Baik',
            'language' => 'id',
            'status' => 'reading',
            'review' => 'Buku ini goblok sekali',
        ])
        ->assertSessionHasErrors('review');

    $this->assertDatabaseCount('books', 0);
});

it('tidak menolak kata normal yang mirip substring kata kasar', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('books.store'), [
            'title' => 'Kelas Klasik',
            'author' => 'Penulis Baik',
            'language' => 'id',
            'status' => 'reading',
            'review' => 'Pembahasan kelas dan passion di buku ini bagus.',
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('books', 1);
});
