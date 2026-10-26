<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Izinkan rating setengah bintang (1, 1.5, 2 ... 5).
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->decimal('rating', 2, 1)->nullable()->change();
        });
    }

    /**
     * Kembalikan ke bilangan bulat.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->unsignedTinyInteger('rating')->nullable()->change();
        });
    }
};
