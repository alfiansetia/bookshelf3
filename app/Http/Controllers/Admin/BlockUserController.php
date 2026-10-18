<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlockUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class BlockUserController extends Controller
{
    /**
     * Blokir akun pengguna beserta alasan yang ditulis admin.
     */
    public function __invoke(BlockUserRequest $request, User $user): RedirectResponse
    {
        $admin = $request->user();

        if ($admin instanceof User && $user->is($admin)) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Kamu tidak bisa memblokir akunmu sendiri.',
            ]);

            return back();
        }

        if ($user->isAdmin()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Akun admin tidak bisa diblokir. Turunkan dulu perannya menjadi pengguna.',
            ]);

            return back();
        }

        if ($user->isBlocked()) {
            Inertia::flash('toast', [
                'type' => 'info',
                'message' => 'Akun '.$user->name.' sudah diblokir sebelumnya.',
            ]);

            return back();
        }

        $user->block(
            (string) $request->validated('reason'),
            $admin instanceof User ? $admin : null,
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Akun '.$user->name.' diblokir. Rak dan profilnya kini menampilkan keterangan blokir.',
        ]);

        return back();
    }
}
