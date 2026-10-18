<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class UnblockUserController extends Controller
{
    /**
     * Buka blokir akun pengguna.
     */
    public function __invoke(User $user): RedirectResponse
    {
        if (! $user->isBlocked()) {
            Inertia::flash('toast', [
                'type' => 'info',
                'message' => 'Akun '.$user->name.' tidak sedang diblokir.',
            ]);

            return back();
        }

        $user->unblock();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Blokir akun '.$user->name.' sudah dibuka.',
        ]);

        return back();
    }
}
