<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class UpdateUserRoleController extends Controller
{
    /**
     * Ubah peran pengguna menjadi admin atau kembali menjadi pengguna biasa.
     */
    public function __invoke(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $admin = $request->user();
        $role = UserRole::from((string) $request->validated('role'));

        if ($admin instanceof User && $user->is($admin)) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Kamu tidak bisa mengubah peranmu sendiri, minta admin lain yang melakukannya.',
            ]);

            return back();
        }

        if ($role === $user->role) {
            return back();
        }

        $user->forceFill(['role' => $role])->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Peran '.$user->name.' sekarang '.$role->label().'.',
        ]);

        return back();
    }
}
