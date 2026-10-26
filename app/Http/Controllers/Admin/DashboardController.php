<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\User;
use App\ShelfVisibility;
use App\UserRole;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Ringkasan operasional untuk admin, terpisah dari dashboard personal.
     */
    public function __invoke(Request $request): Response
    {
        $recentUsers = User::query()
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'name', 'username', 'email', 'role', 'blocked_at', 'created_at']);

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'users' => User::query()->count(),
                'admins' => User::query()->where('role', UserRole::Admin)->count(),
                'blocked' => User::query()->whereNotNull('blocked_at')->count(),
                'books' => Book::query()->count(),
                'finished' => Book::query()->where('status', 'finished')->count(),
                'publicShelves' => User::query()->publicShelf()->count(),
                'unlistedShelves' => User::query()
                    ->where('visibility', ShelfVisibility::Unlisted)
                    ->whereNotNull('username')
                    ->whereNull('blocked_at')
                    ->count(),
                'privateShelves' => User::query()
                    ->where('visibility', ShelfVisibility::Private)
                    ->count(),
            ],
            'recentUsers' => $recentUsers->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role->value,
                'role_label' => $user->role->label(),
                'is_blocked' => $user->isBlocked(),
                'joined_at' => $user->created_at?->toIso8601String(),
                'shelf_url' => $user->hasUsername() ? route('shelf.show', $user->username) : null,
            ])->all(),
        ]);
    }
}
