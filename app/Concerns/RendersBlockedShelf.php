<?php

namespace App\Concerns;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman keterangan untuk rak milik akun yang diblokir admin.
 *
 * Alasannya ditampilkan apa adanya supaya pengunjung rak sekaligus pemiliknya
 * tahu kenapa akun tersebut tidak bisa diakses.
 */
trait RendersBlockedShelf
{
    /**
     * Tanggapan rak yang diblokir beserta alasan dari admin.
     */
    protected function blockedShelf(User $user, string $context): Response
    {
        return Inertia::render('public/Blocked', [
            'profile' => [
                'username' => $user->username,
                'name' => $user->name,
                'avatar_url' => $user->avatar_url,
            ],
            'context' => $context,
            'reason' => $user->blockedMessage(),
            'blockedAt' => $user->blocked_at?->toIso8601String(),
            'canonical' => route('shelf.show', $user->username),
        ]);
    }
}
