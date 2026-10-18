<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Daftar pengguna beserta status blokir dan perannya.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $search = is_string($search) && trim($search) !== '' ? trim($search) : null;

        $status = $request->query('status');
        $status = is_string($status) && in_array($status, ['active', 'blocked'], true)
            ? $status
            : 'all';

        $role = $request->query('role');
        $role = is_string($role) ? UserRole::tryFrom($role) : null;

        $users = User::query()
            ->with('blockedBy:id,name')
            ->withCount('books')
            ->when($search !== null, function ($query) use ($search): void {
                $like = '%'.$search.'%';

                $query->where(function ($query) use ($like): void {
                    $query->where('name', 'like', $like)
                        ->orWhere('username', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->when($status === 'blocked', fn ($query) => $query->whereNotNull('blocked_at'))
            ->when($status === 'active', fn ($query) => $query->whereNull('blocked_at'))
            ->when($role !== null, fn ($query) => $query->where('role', $role))
            ->orderByDesc('blocked_at')
            ->orderByDesc('id')
            ->paginate((int) config('bookshelf.pagination.users'))
            ->withQueryString()
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'role' => $user->role->value,
                'role_label' => $user->role->label(),
                'books_count' => (int) $user->books_count,
                'is_blocked' => $user->isBlocked(),
                'blocked_at' => $user->blocked_at?->toIso8601String(),
                'blocked_reason' => $user->blocked_reason,
                'blocked_by' => $user->blockedBy?->name,
                'shelf_url' => $user->hasUsername() ? route('shelf.show', $user->username) : null,
            ]);

        return Inertia::render('admin/Users', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'role' => $role?->value ?? 'all',
            ],
            'roles' => UserRole::options(),
            'reasonMaxLength' => (int) config('bookshelf.moderation.reason_max'),
            'stats' => [
                'total' => User::query()->count(),
                'admins' => User::query()->where('role', UserRole::Admin)->count(),
                'blocked' => User::query()->whereNotNull('blocked_at')->count(),
            ],
            'currentUserId' => $request->user()?->getKey(),
        ]);
    }
}
