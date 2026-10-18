<?php

use App\Models\User;

it('mencatat waktu username saat onboarding', function () {
    $user = User::factory()->withoutUsername()->create();

    $this->actingAs($user)
        ->put(route('onboarding.username.update'), ['username' => 'pembaca-baru'])
        ->assertRedirect(route('dashboard'));

    $user->refresh();

    expect($user->username)->toBe('pembaca-baru')
        ->and($user->username_changed_at)->not->toBeNull();
});

it('langsung membatasi penggantian username setelah onboarding', function () {
    $user = User::factory()->withoutUsername()->create();

    $this->actingAs($user)
        ->put(route('onboarding.username.update'), ['username' => 'pembaca-baru']);

    $user->refresh();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'username' => 'pembaca-lain',
        ])
        ->assertSessionHasErrors('username');
});
