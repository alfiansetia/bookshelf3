<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('menghapus foto profil dan menandai pilihan tanpa foto', function () {
    Storage::fake('public');

    Storage::disk('public')->put('avatars/lama.webp', 'isi');

    $user = User::factory()->create(['avatar_path' => 'avatars/lama.webp']);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'remove_avatar' => true,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->avatar_path)->toBeNull()
        ->and($user->avatar_removed_at)->not->toBeNull();

    Storage::disk('public')->assertMissing('avatars/lama.webp');
});

it('menyimpan foto unggahan dan membatalkan pilihan tanpa foto', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'avatar_path' => null,
        'avatar_removed_at' => now(),
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'avatar' => UploadedFile::fake()->image('foto.jpg', 300, 300),
        ])
        ->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->avatar_path)->not->toBeNull()
        ->and($user->avatar_removed_at)->toBeNull();

    Storage::disk('public')->assertExists($user->avatar_path);
});
