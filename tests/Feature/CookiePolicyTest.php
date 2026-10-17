<?php

use Inertia\Testing\AssertableInertia as Assert;

it('menampilkan halaman kebijakan cookie', function () {
    $this->get('/kebijakan-cookie')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('legal/Cookie')
            ->where('sessionCookie', config('session.cookie'))
            ->where('canonical', route('legal.cookie'))
            ->has('updatedAt'),
        );
});

it('tidak menabrak pola username pada rak publik', function () {
    $this->get('/kebijakan-cookie')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('legal/Cookie'));
});
