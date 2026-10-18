<?php

use App\Models\User;

it('mencatat waktu ketika username diganti', function () {
    $user = User::factory()->create(['username' => 'pembaca-lama']);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'username' => 'pembaca-baru',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->username)->toBe('pembaca-baru')
        ->and($user->username_changed_at)->not->toBeNull();
});

it('menolak ganti username sebelum cooldown selesai', function () {
    $user = User::factory()->create([
        'username' => 'pembaca-lama',
        'username_changed_at' => now()->subDays(2),
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'username' => 'pembaca-baru',
        ])
        ->assertSessionHasErrors('username');

    expect($user->refresh()->username)->toBe('pembaca-lama');
});

it('mengizinkan ganti username setelah cooldown lewat', function () {
    $user = User::factory()->create([
        'username' => 'pembaca-lama',
        'username_changed_at' => now()->subDays(4),
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'username' => 'pembaca-baru',
        ])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->username)->toBe('pembaca-baru');
});

it('tidak menghitung simpan tanpa perubahan sebagai ganti username', function () {
    $changedAt = now()->subDay()->startOfSecond();

    $user = User::factory()->create([
        'username' => 'pembaca-lama',
        'username_changed_at' => $changedAt,
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Nama Baru',
            'email' => $user->email,
            'username' => 'pembaca-lama',
        ])
        ->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Nama Baru')
        ->and($user->username_changed_at?->timestamp)->toBe($changedAt->timestamp);
});
