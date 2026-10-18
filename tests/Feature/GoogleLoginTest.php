<?php

use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

it('mengimpor foto Google sebagai WebP saat profil masih kosong', function () {
    Storage::fake('public');

    Http::fake(['*' => Http::response(
        UploadedFile::fake()->image('foto.jpg', 900, 900)->getContent(),
        200,
        ['Content-Type' => 'image/jpeg'],
    )]);

    Socialite::fake('google', SocialiteUser::fake([
        'id' => '555444333',
        'name' => 'Pembaca Baru',
        'email' => 'baru@example.com',
        'avatar' => 'https://lh3.googleusercontent.com/a/abc=s96-c',
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('onboarding.username.edit'));

    $path = (string) User::query()->where('email', 'baru@example.com')->sole()->avatar_path;
    $size = getimagesize(Storage::disk('public')->path($path));

    expect($path)->toEndWith('.webp')
        ->and([$size[0], $size[1]])->toBe([512, 512])
        ->and(Storage::disk('public')->mimeType($path))->toBe('image/webp');

    // Foto Google diminta dalam ukuran besar supaya tidak pecah saat ditampilkan.
    $expected = 'https://lh3.googleusercontent.com/a/abc=s'.config('bookshelf.avatar.max_width').'-c';

    Http::assertSent(fn (Request $request): bool => $request->url() === $expected);
});

it('mengisi avatar pengguna lama yang masih kosong', function () {
    Storage::fake('public');

    Http::fake(['*' => Http::response(
        UploadedFile::fake()->image('foto.jpg', 300, 300)->getContent(),
        200,
        ['Content-Type' => 'image/jpeg'],
    )]);

    $user = User::factory()->create([
        'email' => 'kosong@example.com',
        'username' => 'tanpa-foto',
        'google_id' => '222333444',
        'avatar_path' => null,
    ]);

    Socialite::fake('google', SocialiteUser::fake([
        'id' => '222333444',
        'name' => 'Tanpa Foto',
        'email' => 'kosong@example.com',
        'avatar' => 'https://lh3.googleusercontent.com/a/xyz=s96-c',
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('dashboard'));

    $path = (string) $user->refresh()->avatar_path;

    expect($path)->toEndWith('.webp')
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});

it('tidak menimpa avatar yang sudah dipasang pengguna', function () {
    Storage::fake('public');
    Http::fake();

    $user = User::factory()->create([
        'email' => 'punya@example.com',
        'username' => 'punya-foto',
        'google_id' => '777888999',
        'avatar_path' => 'avatars/lama.jpg',
    ]);

    Socialite::fake('google', SocialiteUser::fake([
        'id' => '777888999',
        'name' => 'Punya Foto',
        'email' => 'punya@example.com',
        'avatar' => 'https://lh3.googleusercontent.com/a/xyz=s96-c',
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('dashboard'));

    expect($user->refresh()->avatar_path)->toBe('avatars/lama.jpg');

    Http::assertNothingSent();
});

it('tidak memasang ulang foto Google setelah pengguna memilih tanpa foto', function () {
    Storage::fake('public');
    Http::fake();

    $user = User::factory()->create([
        'email' => 'tanpa-foto@example.com',
        'username' => 'tanpa-foto',
        'google_id' => '123321123',
        'avatar_path' => null,
        'avatar_removed_at' => now(),
    ]);

    Socialite::fake('google', SocialiteUser::fake([
        'id' => '123321123',
        'name' => 'Tanpa Foto',
        'email' => 'tanpa-foto@example.com',
        'avatar' => 'https://lh3.googleusercontent.com/a/xyz=s96-c',
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('dashboard'));

    expect($user->refresh()->avatar_path)->toBeNull();

    Http::assertNothingSent();
});

it('mengabaikan foto Google yang bukan gambar', function () {
    Storage::fake('public');

    Http::fake(['*' => Http::response('<html>bukan gambar</html>', 200, ['Content-Type' => 'text/html'])]);

    Socialite::fake('google', SocialiteUser::fake([
        'id' => '666777888',
        'name' => 'Bukan Gambar',
        'email' => 'aneh@example.com',
        'avatar' => 'https://lh3.googleusercontent.com/a/aneh=s96-c',
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('onboarding.username.edit'));

    $this->assertAuthenticated();

    expect(User::query()->where('email', 'aneh@example.com')->sole()->avatar_path)->toBeNull();
});
