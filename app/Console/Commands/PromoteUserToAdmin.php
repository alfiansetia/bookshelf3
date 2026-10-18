<?php

namespace App\Console\Commands;

use App\Models\User;
use App\UserRole;
use Illuminate\Console\Command;

/**
 * Menjadikan pengguna sebagai admin (atau menurunkannya kembali).
 *
 * Dipakai untuk admin pertama, karena hanya admin yang bisa mengubah peran
 * lewat halaman kelola pengguna.
 */
class PromoteUserToAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:promote
                            {email : Email pengguna yang ingin diubah perannya}
                            {--demote : Turunkan kembali menjadi pengguna biasa}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jadikan pengguna admin, atau turunkan lagi dengan opsi --demote';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $this->components->error("Pengguna dengan email {$email} tidak ditemukan.");

            return self::FAILURE;
        }

        $role = $this->option('demote') ? UserRole::User : UserRole::Admin;

        if ($user->role === $role) {
            $this->components->info("Peran {$user->name} sudah {$role->label()}.");

            return self::SUCCESS;
        }

        $user->forceFill(['role' => $role])->save();

        $this->components->info("Peran {$user->name} sekarang {$role->label()}.");

        return self::SUCCESS;
    }
}
