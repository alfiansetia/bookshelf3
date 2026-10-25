<?php

use Inertia\Testing\AssertableInertia as Assert;

it('menampilkan halaman kebijakan privasi', function () {
    $this->get('/kebijakan-privasi')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('legal/Privacy')
            ->where('canonical', route('legal.privacy'))
            ->has('updatedAt'),
        );
});

it('menampilkan halaman kontak beserta info pengelola', function () {
    $this->get('/kontak')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('legal/Contact')
            ->where('canonical', route('legal.contact'))
            ->where('email', 'alfian@kacangan.net')
            ->has('socials', 3),
        );
});

it('menampilkan halaman faq', function () {
    $this->get('/faq')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('legal/Faq')
            ->where('canonical', route('legal.faq'))
            ->has('updatedAt'),
        );
});

it('tidak menabrak pola username pada rak publik', function () {
    $this->get('/kebijakan-privasi')->assertOk()->assertInertia(fn (Assert $page) => $page->component('legal/Privacy'));
    $this->get('/kontak')->assertOk()->assertInertia(fn (Assert $page) => $page->component('legal/Contact'));
    $this->get('/faq')->assertOk()->assertInertia(fn (Assert $page) => $page->component('legal/Faq'));
});

it('menyertakan halaman legal baru di sitemap', function () {
    $content = (string) $this->get('/sitemap.xml')->assertOk()->getContent();

    expect($content)
        ->toContain(route('legal.privacy'))
        ->toContain(route('legal.contact'))
        ->toContain(route('legal.faq'));
});
