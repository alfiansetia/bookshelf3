<?php

namespace App\Http\Controllers\Onboarding;

use App\Concerns\ProfileValidationRules;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsernameRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UsernameController extends Controller
{
    use ProfileValidationRules;

    /**
     * Tampilkan langkah pemilihan username publik.
     */
    public function edit(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasUsername()) {
            return to_route('dashboard');
        }

        return Inertia::render('onboarding/Username', [
            'suggestion' => $this->suggestUsername($user),
            'rules' => [
                'min' => (int) config('bookshelf.username.min'),
                'max' => (int) config('bookshelf.username.max'),
            ],
            'availabilityUrl' => route('onboarding.username.availability'),
        ]);
    }

    /**
     * Simpan username yang dipilih lalu antar ke dashboard.
     */
    public function update(StoreUsernameRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->forceFill([
            'username' => $request->validated('username'),
            'username_changed_at' => now(),
        ])->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Rak kamu sudah aktif di '.config('app.url').'/'.$user->username,
        ]);

        return to_route('dashboard');
    }

    /**
     * Usulan username dari nama atau email pengguna.
     */
    private function suggestUsername(User $user): string
    {
        $base = Str::of($user->name ?: Str::before($user->email, '@'))
            ->slug()
            ->limit((int) config('bookshelf.username.max') - 4, '')
            ->value();

        if (mb_strlen($base) < (int) config('bookshelf.username.min')) {
            $base = 'pembaca';
        }

        $candidate = $base;
        $suffix = 1;

        while (User::query()->where('username', $candidate)->exists()) {
            $candidate = $base.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
