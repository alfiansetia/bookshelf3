<?php

use App\Models\Book;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('membagikan data SEO bawaan ke setiap halaman', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('seo.siteName', config('bookshelf.seo.title_suffix'))
            ->where('seo.description', config('bookshelf.seo.description'))
            ->where('seo.image', url(config('bookshelf.seo.image')))
            ->where('seo.locale', config('bookshelf.seo.locale')),
        );
});

it('mengirim alamat kanonik pada halaman publik statis', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('canonical', route('home')));

    $this->get('/jelajah')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('canonical', route('explore')));

    $this->get('/kebijakan-cookie')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('canonical', route('legal.cookie')));
});

it('mengirim alamat kanonik rak dan buku publik', function () {
    $owner = User::factory()->create(['username' => 'pembaca-hebat']);
    $book = Book::factory()->for($owner)->create();

    $this->get('/pembaca-hebat')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('canonical', route('shelf.show', 'pembaca-hebat')),
        );

    $this->get("/pembaca-hebat/{$book->slug}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('canonical', route('shelf.book.show', [
                'username' => 'pembaca-hebat',
                'book' => $book->slug,
            ])),
        );
});

it('menyajikan sitemap XML berisi halaman publik', function () {
    $owner = User::factory()->create(['username' => 'pembaca-hebat']);
    $book = Book::factory()->for($owner)->create();

    $response = $this->get('/sitemap.xml');

    $response->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $content = (string) $response->getContent();

    expect($content)
        ->toContain(route('home'))
        ->toContain(route('explore'))
        ->toContain(route('legal.cookie'))
        ->toContain(route('shelf.show', 'pembaca-hebat'))
        ->toContain(route('shelf.book.show', [
            'username' => 'pembaca-hebat',
            'book' => $book->slug,
        ]));

    expect($content)->toStartWith('<?xml version="1.0" encoding="UTF-8"?>');
    expect(simplexml_load_string($content))->not->toBeFalse();
});

it('tidak memasukkan rak privat ke sitemap', function () {
    $owner = User::factory()->privateShelf()->create(['username' => 'rak-rahasia']);
    Book::factory()->for($owner)->create();

    $content = (string) $this->get('/sitemap.xml')->assertOk()->getContent();

    expect($content)
        ->not->toContain('rak-rahasia')
        ->not->toContain(route('shelf.show', 'rak-rahasia'));
});

it('menyajikan robots.txt beserta alamat sitemap', function () {
    $response = $this->get('/robots.txt');

    $response->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

    expect((string) $response->getContent())
        ->toContain('User-agent: *')
        ->toContain('Disallow: /dashboard')
        ->toContain('Sitemap: '.route('sitemap'));
});
