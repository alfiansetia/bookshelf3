<?php

use App\Models\AccountDeletion;
use App\Models\User;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

it('mencatat tombstone saat akun dihapus', function () {
    $user = User::factory()->create([
        'google_id' => 'google-hapus-1',
        'username' => 'rak-dihapus',
    ]);

    $this->actingAs($user)
        ->delete(route('profile.destroy'))
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);

    $tombstone = AccountDeletion::query()->where('google_id', 'google-hapus-1')->sole();

    expect($tombstone->username)->toBe('rak-dihapus')
        ->and($tombstone->isActive())->toBeTrue();
});

it('menolak login ulang dalam masa tenggang hapus akun', function () {
    $user = User::factory()->create([
        'google_id' => 'google-hapus-2',
        'email' => 'hapus@example.com',
        'username' => 'rak-hapus-dua',
    ]);

    $this->actingAs($user)->delete(route('profile.destroy'));

    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-hapus-2',
        'name' => 'Penghapus',
        'email' => 'hapus@example.com',
        'avatar' => null,
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'hapus@example.com']);
});

it('mengizinkan daftar lagi setelah masa tenggang lewat', function () {
    AccountDeletion::create([
        'google_id' => 'google-hapus-3',
        'email' => 'kembali@example.com',
        'username' => 'rak-lama',
        'deleted_at' => now()->subDays(31),
    ]);

    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-hapus-3',
        'name' => 'Kembali Lagi',
        'email' => 'kembali@example.com',
        'avatar' => null,
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('onboarding.username.edit'));

    $this->assertAuthenticated();
    $this->assertDatabaseMissing('account_deletions', ['google_id' => 'google-hapus-3']);
});

it('menolak username bekas yang masih dalam masa tenggang', function () {
    AccountDeletion::create([
        'google_id' => 'google-hapus-4',
        'email' => 'lama@example.com',
        'username' => 'bekas-rak',
        'deleted_at' => now(),
    ]);

    $user = User::factory()->withoutUsername()->create();

    $this->actingAs($user)
        ->put(route('onboarding.username.update'), ['username' => 'bekas-rak'])
        ->assertSessionHasErrors('username');

    expect($user->fresh()->username)->toBeNull();
});

it('membebaskan username setelah masa tenggang lewat', function () {
    AccountDeletion::create([
        'google_id' => 'google-hapus-5',
        'email' => 'bebas@example.com',
        'username' => 'bebas-rak',
        'deleted_at' => now()->subDays(31),
    ]);

    $user = User::factory()->withoutUsername()->create();

    $this->actingAs($user)
        ->put(route('onboarding.username.update'), ['username' => 'bebas-rak'])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->username)->toBe('bebas-rak');
});
