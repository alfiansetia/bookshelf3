<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => $this->uniqueUsername(),
            'email' => fake()->unique()->safeEmail(),
            'google_id' => fake()->unique()->numerify('#####################'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'theme' => 'wood',
            'is_public' => true,
        ];
    }

    /**
     * Username acak yang aman dipakai sebagai URL rak.
     */
    protected function uniqueUsername(): string
    {
        $base = Str::limit(Str::slug(fake()->unique()->userName()) ?: 'pembaca', 24, '');

        return $base.fake()->unique()->numerify('###');
    }

    /**
     * Pengguna yang belum memilih username publik.
     */
    public function withoutUsername(): static
    {
        return $this->state(fn (array $attributes) => [
            'username' => null,
        ]);
    }

    /**
     * Rak yang disembunyikan dari publik.
     */
    public function privateShelf(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }
}
