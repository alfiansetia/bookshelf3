<?php

use App\BookStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->unsignedSmallInteger('published_year')->nullable();
            $table->string('language', 16)->default('id');
            $table->string('status', 32)->default(BookStatus::WantToRead->value);
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('synopsis')->nullable();
            $table->text('review')->nullable();
            $table->string('cover_path')->nullable();
            $table->unsignedSmallInteger('total_pages')->nullable();
            $table->date('started_at')->nullable();
            $table->date('finished_at')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'language']);
            $table->index(['user_id', 'is_favorite']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
