<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('membuka dashboard admin untuk admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Dashboard')
            ->has('stats', 8)
            ->has('recentUsers'),
        );
});

it('mengalihkan non-admin dari dashboard admin ke dashboard personal', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')
        ->assertRedirect(route('dashboard'));
});
