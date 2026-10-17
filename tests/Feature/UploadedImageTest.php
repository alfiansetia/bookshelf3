<?php

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('memperkecil dan mengompres sampul yang diunggah', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $cover = UploadedFile::fake()->image('sampul.jpg', 2000, 3000);
    $originalSize = (int) $cover->getSize();

    $this->actingAs($user)
        ->post(route('books.store'), [
            'title' => 'Buku Bersampul',
            'author' => 'Penulis',
            'language' => 'id',
            'status' => 'reading',
            'cover' => $cover,
        ])
        ->assertSessionHasNoErrors();

    $path = (string) Book::query()->whereBelongsTo($user)->sole()->cover_path;
    $size = getimagesize(Storage::disk('public')->path($path));

    expect($path)->toEndWith('.webp')
        ->and([$size[0], $size[1]])->toBe([1000, 1500])
        ->and(Storage::disk('public')->size($path))->toBeLessThan($originalSize);
});

it('memperkecil avatar yang diunggah', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('foto.jpg', 1500, 2000),
        ])
        ->assertSessionHasNoErrors();

    $path = (string) $user->refresh()->avatar_path;
    $size = getimagesize(Storage::disk('public')->path($path));

    expect($path)->toEndWith('.webp')
        ->and([$size[0], $size[1]])->toBe([384, 512]);
});

it('menolak sampul yang dimensinya terlalu besar', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('books.store'), [
            'title' => 'Buku Terlalu Besar',
            'author' => 'Penulis',
            'language' => 'id',
            'status' => 'reading',
            'cover' => UploadedFile::fake()->image('sampul.jpg', 6001, 10),
        ])
        ->assertSessionHasErrors('cover');

    $this->assertDatabaseCount('books', 0);
});

it('menolak avatar yang dimensinya terlalu besar', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('foto.jpg', 10, 6001),
        ])
        ->assertSessionHasErrors('avatar');

    expect($user->refresh()->avatar_path)->toBeNull();
});
