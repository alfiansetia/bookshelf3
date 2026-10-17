<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Catatan: trait WithoutModelEvents tidak dipakai karena slug buku
     * dibuat lewat event `saving` pada model Book.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BookSeeder::class,
        ]);
    }
}
