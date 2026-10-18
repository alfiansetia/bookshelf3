<?php

use App\Models\Book;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Storage;

it('mengisi rak demo dengan buku bersinopsis dan berulasan', function () {
    Storage::fake('public');

    $this->seed(DatabaseSeeder::class);

    $owner = User::query()->where('username', 'demo')->firstOrFail();
    $books = Book::query()->where('user_id', $owner->id)->get();

    expect($owner->is_public)->toBeTrue()
        ->and($books)->not->toBeEmpty()
        ->and($books->every(fn (Book $book) => filled($book->synopsis)))->toBeTrue();

    $reviewed = $books->whereNotNull('review');

    expect($reviewed)->not->toBeEmpty()
        ->and($reviewed->every(fn (Book $book) => filled($book->review)))->toBeTrue();
});

it('tidak menggandakan atau mengubah isi rak demo saat seeder dijalankan ulang', function () {
    Storage::fake('public');

    $this->seed(DatabaseSeeder::class);

    $owner = User::query()->where('username', 'demo')->firstOrFail();
    $before = Book::query()
        ->where('user_id', $owner->id)
        ->orderBy('title')
        ->pluck('review', 'title');

    $this->seed(DatabaseSeeder::class);

    $after = Book::query()
        ->where('user_id', $owner->id)
        ->orderBy('title')
        ->pluck('review', 'title');

    expect(User::query()->count())->toBe(4)
        ->and($after->count())->toBe($before->count())
        ->and($after->all())->toBe($before->all());
});
