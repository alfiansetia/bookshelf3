<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cache hasil pencarian metadata buku dari provider eksternal
     * (OpenLibrary). Data buku statis, jadi disimpan lama dan
     * request berikutnya dilayani dari DB tanpa hit API.
     */
    public function up(): void
    {
        Schema::create('book_lookup_cache', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32)->default('openlibrary');
            $table->char('query_hash', 32);
            $table->string('query', 255);
            $table->json('results');
            $table->unsignedInteger('hits')->default(1);
            $table->timestamp('fetched_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'query_hash']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_lookup_cache');
    }
};
