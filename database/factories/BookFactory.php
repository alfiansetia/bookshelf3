<?php

namespace Database\Factories;

use App\BookStatus;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(BookStatus::cases());
        $publishedYear = fake()->numberBetween(1975, (int) date('Y'));

        return [
            'user_id' => User::factory(),
            'title' => $this->fakeTitle(),
            'author' => fake()->name(),
            'publisher' => fake()->optional(0.7)->company(),
            'published_year' => $publishedYear,
            'language' => fake()->randomElement(array_keys(config('bookshelf.languages'))),
            'status' => $status->value,
            'rating' => $status === BookStatus::Finished ? fake()->randomElement([1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5]) : null,
            'synopsis' => fake()->paragraphs(2, true),
            'review' => fake()->optional(0.6)->paragraphs(2, true),
            'cover_path' => null,
            'total_pages' => fake()->numberBetween(90, 900),
            'started_at' => null,
            'finished_at' => null,
            'is_favorite' => fake()->boolean(20),
            'position' => 0,
        ];
    }

    /**
     * Judul buku tiruan dari 2–4 kata.
     */
    private function fakeTitle(): string
    {
        $words = fake()->words(fake()->numberBetween(2, 4), true);

        return Str::title(is_string($words) ? $words : implode(' ', $words));
    }

    /**
     * Buku yang sudah tamat dibaca.
     */
    public function finished(): static
    {
        return $this->state(function (array $attributes): array {
            $finishedAt = fake()->dateTimeBetween('-3 years', 'now');

            return [
                'status' => BookStatus::Finished->value,
                'rating' => fake()->randomElement([1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5]),
                'started_at' => $finishedAt->modify('-'.fake()->numberBetween(5, 40).' days'),
                'finished_at' => $finishedAt,
            ];
        });
    }

    /**
     * Buku yang sedang dibaca.
     */
    public function reading(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => BookStatus::Reading->value,
            'rating' => null,
            'started_at' => fake()->dateTimeBetween('-4 months', 'now'),
            'finished_at' => null,
        ]);
    }

    /**
     * Buku yang masih masuk daftar buruan.
     */
    public function wantToRead(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => BookStatus::WantToRead->value,
            'rating' => null,
            'started_at' => null,
            'finished_at' => null,
        ]);
    }

    /**
     * Tandai sebagai buku favorit.
     */
    public function favorite(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_favorite' => true,
        ]);
    }

    /**
     * Pakai bahasa Indonesia.
     */
    public function indonesian(): static
    {
        return $this->state(fn (array $attributes): array => [
            'language' => 'id',
        ]);
    }
}
