<?php

use App\ShelfTheme;
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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->nullable()->unique()->after('name');
            $table->string('avatar_path')->nullable()->after('google_id');
            $table->string('tagline', 120)->nullable()->after('avatar_path');
            $table->text('bio')->nullable()->after('tagline');
            $table->string('theme', 32)->default(ShelfTheme::Wood->value)->after('bio');
            $table->boolean('is_public')->default(true)->after('theme');
            $table->string('social_instagram')->nullable();
            $table->string('social_tiktok')->nullable();
            $table->string('social_x')->nullable();
            $table->string('social_youtube')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('social_website')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);

            $table->dropColumn([
                'username',
                'avatar_path',
                'tagline',
                'bio',
                'theme',
                'is_public',
                'social_instagram',
                'social_tiktok',
                'social_x',
                'social_youtube',
                'social_linkedin',
                'social_website',
            ]);
        });
    }
};
