<?php

use App\UserRole;
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
            // Data yang sudah ada otomatis menjadi pengguna biasa.
            $table->string('role', 20)->default(UserRole::User->value)->after('email');
            $table->timestamp('blocked_at')->nullable()->after('username_changed_at');
            $table->text('blocked_reason')->nullable()->after('blocked_at');
            $table->foreignId('blocked_by_id')->nullable()->after('blocked_reason')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('blocked_by_id');

            $table->dropColumn(['role', 'blocked_at', 'blocked_reason']);
        });
    }
};
