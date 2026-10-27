<?php

use App\Models\BookLookupCache;
use App\Models\User;
use Illuminate\Support\Facades\Http;

it('mengutamakan google books lalu menyimpannya ke db', function () {
    $user = User::factory()->create();

    Http::fake([
        'googleapis.com/*' => Http::response([
            'items' => [
                [
                    'id' => 'abc123',
                    'volumeInfo' => [
                        'title' => 'Entrok',
                        'authors' => ['Okky Madasari'],
                        'publisher' => 'Gramedia',
                        'publishedDate' => '2010',
                        'pageCount' => 280,
                        'language' => 'id',
                        'industryIdentifiers' => [
                            ['type' => 'ISBN_13', 'identifier' => '9789792296761'],
                        ],
                        'imageLinks' => ['thumbnail' => 'http://books.google.com/thumb.jpg?zoom=1'],
                    ],
                ],
            ],
        ], 200),
        'openlibrary.org/*' => function () {
            throw new RuntimeException('OpenLibrary tidak boleh dipanggil saat Google ada hasil');
        },
    ]);

    $first = $this->actingAs($user)
        ->getJson(route('books.lookup', ['q' => 'entrok']))
        ->assertOk()
        ->json();

    expect($first['source'])->toBe('api')
        ->and($first['provider'])->toBe('google')
        ->and($first['data'][0]['title'])->toBe('Entrok')
        ->and($first['data'][0]['author'])->toBe('Okky Madasari')
        ->and($first['data'][0]['published_year'])->toBe(2010)
        ->and($first['data'][0]['language'])->toBe('id')
        ->and($first['data'][0]['cover_url'])->toStartWith('https://');

    $this->assertDatabaseHas('book_lookup_cache', [
        'provider' => 'google',
        'query_hash' => md5('entrok'),
    ]);

    // Request kedua wajib dari DB tanpa hit API lagi.
    Http::fake(function () {
        throw new RuntimeException('API tidak boleh dipanggil untuk cache hit');
    });

    $second = $this->actingAs($user)
        ->getJson(route('books.lookup', ['q' => 'Entrok']))
        ->assertOk()
        ->json();

    expect($second['source'])->toBe('db')
        ->and($second['provider'])->toBe('google')
        ->and($second['data'][0]['title'])->toBe('Entrok');

    expect(BookLookupCache::query()->firstOrFail()->hits)->toBe(2);
});

it('fallback ke openlibrary saat google kosong', function () {
    $user = User::factory()->create();

    Http::fake([
        'googleapis.com/*' => Http::response(['items' => []], 200),
        'openlibrary.org/*' => Http::response([
            'docs' => [
                [
                    'key' => '/works/OL1W',
                    'title' => 'Bumi Manusia',
                    'author_name' => ['Pramoedya Ananta Toer'],
                    'first_publish_year' => 1980,
                    'publisher' => ['Hasta Mitra'],
                    'number_of_pages_median' => 535,
                    'cover_i' => 123,
                    'isbn' => ['9789799731234'],
                    'language' => ['ind'],
                ],
            ],
        ], 200),
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('books.lookup', ['q' => 'bumi manusia']))
        ->assertOk()
        ->json();

    expect($response['source'])->toBe('api')
        ->and($response['provider'])->toBe('openlibrary')
        ->and($response['data'][0]['title'])->toBe('Bumi Manusia')
        ->and($response['data'][0]['language'])->toBe('id');
});

it('membuang hasil google yang judul maupun penulisnya tak cocok', function () {
    $user = User::factory()->create();

    Http::fake([
        'googleapis.com/*' => Http::response([
            'items' => [
                [
                    'id' => 'junk1',
                    'volumeInfo' => [
                        'title' => 'Intensif Bedah Materi Dan Soal UTBK-SNBT 2024',
                        'authors' => ['Tim Eureka Edutech'],
                        'language' => 'id',
                    ],
                ],
                [
                    'id' => 'good1',
                    'volumeInfo' => [
                        'title' => 'Entrok',
                        'authors' => ['Okky Madasari'],
                        'publisher' => 'Gramedia',
                        'publishedDate' => '2010',
                        'language' => 'id',
                    ],
                ],
            ],
        ], 200),
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('books.lookup', ['q' => 'entrok']))
        ->assertOk()
        ->json();

    expect($response['provider'])->toBe('google')
        ->and($response['data'])->toHaveCount(1)
        ->and($response['data'][0]['title'])->toBe('Entrok');
});

it('mentoleransi typo ringan seperti berkisar vs bekisar', function () {
    $user = User::factory()->create();

    Http::fake([
        'googleapis.com/*' => Http::response([
            'items' => [
                [
                    'id' => 'typo1',
                    'volumeInfo' => [
                        'title' => 'Bekisar merah',
                        'authors' => ['Ahmad Tohari'],
                        'language' => 'id',
                    ],
                ],
                [
                    'id' => 'junk2',
                    'volumeInfo' => [
                        'title' => 'XPLORE Ulangan Harian SMP Kelas 8',
                        'authors' => ['Tim Foton Edukasi'],
                        'language' => 'id',
                    ],
                ],
            ],
        ], 200),
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('books.lookup', ['q' => 'berkisar merah']))
        ->assertOk()
        ->json();

    expect($response['provider'])->toBe('google')
        ->and($response['data'])->toHaveCount(1)
        ->and($response['data'][0]['title'])->toBe('Bekisar merah');
});

it('menolak query lookup yang terlalu pendek', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('books.lookup', ['q' => 'ab']))
        ->assertUnprocessable();
});
