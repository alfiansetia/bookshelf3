<?php

use App\ShelfVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom visibility dan migrasi dari is_public boolean.
     *
     * public (true) -> public, private (false) -> private.
     * State unlisted hanya bisa dipilih manual setelah migrasi.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('visibility', 16)->default(ShelfVisibility::Public->value)->after('theme');
        });

        DB::table('users')->where('is_public', true)->update(['visibility' => ShelfVisibility::Public->value]);
        DB::table('users')->where('is_public', false)->update(['visibility' => ShelfVisibility::Private->value]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_public')->default(true)->after('theme');
        });

        DB::table('users')->where('visibility', ShelfVisibility::Public->value)->update(['is_public' => true]);
        DB::table('users')->where('visibility', '!=', ShelfVisibility::Public->value)->update(['is_public' => false]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};
