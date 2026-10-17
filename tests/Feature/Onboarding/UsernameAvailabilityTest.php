<?php

use App\Models\User;

it('menyatakan username yang belum dipakai tersedia', function () {
    $user = User::factory()->create(['username' => 'pembaca-lama']);

    $this->actingAs($user)
        ->getJson(route('onboarding.username.availability', ['username' => 'pembaca-baru']))
        ->assertOk()
        ->assertJsonPath('available', true)
        ->assertJsonPath('username', 'pembaca-baru')
        ->assertJsonPath('message', 'Username ini bisa kamu pakai.');
});

it('menyatakan username milik pengguna lain tidak tersedia', function () {
    User::factory()->create(['username' => 'rak-senja']);
    $user = User::factory()->create(['username' => 'pembaca-lama']);

    $this->actingAs($user)
        ->getJson(route('onboarding.username.availability', ['username' => 'rak-senja']))
        ->assertOk()
        ->assertJsonPath('available', false)
        ->assertJsonPath('message', 'Username ini sudah dipakai orang lain.');
});

it('menyatakan username sendiri tetap tersedia', function () {
    $user = User::factory()->create(['username' => 'pembaca-lama']);

    $this->actingAs($user)
        ->getJson(route('onboarding.username.availability', ['username' => 'pembaca-lama']))
        ->assertOk()
        ->assertJsonPath('available', true)
        ->assertJsonPath('message', 'Username ini bisa kamu pakai.');
});

it('menyatakan username terlarang tidak tersedia', function () {
    $user = User::factory()->create(['username' => 'pembaca-lama']);

    $this->actingAs($user)
        ->getJson(route('onboarding.username.availability', ['username' => 'settings']))
        ->assertOk()
        ->assertJsonPath('available', false)
        ->assertJsonPath('message', 'Username ini tidak boleh dipakai, silakan pilih yang lain.');
});

it('menyatakan username berformat salah tidak tersedia', function () {
    $user = User::factory()->create(['username' => 'pembaca-lama']);

    $this->actingAs($user)
        ->getJson(route('onboarding.username.availability', ['username' => 'pembaca baru!']))
        ->assertOk()
        ->assertJsonPath('available', false)
        ->assertJsonPath('message', 'Username hanya boleh berisi huruf kecil, angka, titik, garis bawah, dan tanda hubung.');
});

it('mengalihkan pengunjung tanpa login ke halaman masuk', function () {
    $this->get(route('onboarding.username.availability', ['username' => 'pembaca-baru']))
        ->assertRedirect(route('login'));
});
