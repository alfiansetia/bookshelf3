<?php

use App\Models\User;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

it('mengalihkan pengguna ke halaman persetujuan Google', function () {
    Socialite::fake('google', SocialiteUser::fake());

    $this->get(route('auth.google.redirect'))
        ->assertRedirect('https://socialite.fake/google/authorize');
});

it('membuat akun baru dari callback Google lalu meminta username', function () {
    Socialite::fake('google', SocialiteUser::fake([
        'id' => '987654321',
        'name' => 'Andi Pembaca',
        'email' => 'andi@example.com',
        'avatar' => null,
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('onboarding.username.edit'));

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'andi@example.com')->sole();

    expect($user->google_id)->toBe('987654321')
        ->and($user->username)->toBeNull()
        ->and($user->email_verified_at)->not->toBeNull();
});

it('mengarahkan pengguna Google yang sudah punya username ke dashboard', function () {
    $user = User::factory()->create([
        'email' => 'lama@example.com',
        'username' => 'pembaca-lama',
        'google_id' => '999888777',
        'avatar_path' => 'avatars/lama.jpg',
    ]);

    Socialite::fake('google', SocialiteUser::fake([
        'id' => '111222333',
        'name' => 'Pembaca Lama',
        'email' => 'lama@example.com',
        'avatar' => null,
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);

    expect($user->refresh()->google_id)->toBe('111222333');
});

it('kembali ke halaman masuk bila login Google dibatalkan', function () {
    $this->get(route('auth.google.callback', ['error' => 'access_denied']))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
