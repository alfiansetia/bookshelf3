<?php

namespace App;

/**
 * Peran pengguna di aplikasi.
 *
 * Admin boleh mengelola pengguna lain, pengguna biasa hanya mengurus raknya
 * sendiri. Peran bawaan adalah pengguna biasa supaya data lama tetap aman.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case User = 'user';

    /**
     * Nama peran untuk ditampilkan.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::User => 'Pengguna',
        };
    }

    /**
     * Peran bawaan untuk pendaftar baru.
     */
    public static function default(): self
    {
        return self::User;
    }

    /**
     * Semua peran dalam bentuk array yang siap dikirim ke frontend.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $role): array => [
            'value' => $role->value,
            'label' => $role->label(),
        ], self::cases());
    }
}
