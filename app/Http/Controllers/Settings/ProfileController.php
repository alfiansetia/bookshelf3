<?php

namespace App\Http\Controllers\Settings;

use App\Actions\StoreUploadedImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\AccountDeletion;
use App\Models\User;
use App\UploadedImageType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(private StoreUploadedImage $imageStore) {}

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('settings/Profile', [
            'profile' => [
                'username' => $user->username,
                'tagline' => $user->tagline,
                'bio' => $user->bio,
                'avatar_url' => $user->avatar_url,
            ],
            'shelfUrl' => $user->hasUsername() ? route('shelf.show', $user->username) : null,
            'availabilityUrl' => route('onboarding.username.availability'),
            'usernameRules' => [
                'min' => (int) config('bookshelf.username.min'),
                'max' => (int) config('bookshelf.username.max'),
            ],
            'usernameChange' => [
                'can_change' => $user->canChangeUsername(),
                'available_at' => $user->usernameChangeAvailableAt()?->toIso8601String(),
                'cooldown_days' => (int) config('bookshelf.username.cooldown_days'),
            ],
            'limits' => [
                'tagline' => (int) config('bookshelf.limits.tagline'),
                'bio' => (int) config('bookshelf.limits.bio'),
            ],
            'avatarMaxKilobytes' => (int) config('bookshelf.avatar.max_kilobytes'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $attributes = $request->safe()->except(['avatar', 'remove_avatar']);

        $user->fill($attributes);

        if (filled($attributes['username'] ?? null) && $attributes['username'] !== $user->getOriginal('username')) {
            $user->username_changed_at = now();
        }

        $this->syncAvatar($request, $user);

        $user->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Profil kamu sudah diperbarui.',
        ]);

        return to_route('profile.edit');
    }

    /**
     * Hapus akun pengguna beserta seluruh bukunya.
     *
     * google_id, email, dan username terakhir dicatat sebagai tombstone di
     * `account_deletions` supaya akun tidak bisa langsung didaftarkan ulang
     * untuk menghindari moderasi (lihat `config('bookshelf.deletion')`).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        AccountDeletion::updateOrCreate(
            ['google_id' => $user->google_id],
            [
                'email' => $user->email,
                'username' => $user->username,
                'deleted_at' => now(),
            ],
        );

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Simpan foto baru atau catat pilihan "tanpa foto" milik pengguna.
     *
     * Pilihan tanpa foto disimpan di `avatar_removed_at` supaya foto Google
     * tidak dipasang ulang saat pengguna login kembali.
     */
    private function syncAvatar(ProfileUpdateRequest $request, User $user): void
    {
        if ($request->hasFile('avatar')) {
            $path = $this->imageStore->store($request->file('avatar'), UploadedImageType::Avatar);

            $this->imageStore->delete($user->avatar_path, UploadedImageType::Avatar);

            $user->avatar_path = $path;
            $user->avatar_removed_at = null;

            return;
        }

        if ($request->boolean('remove_avatar') && filled($user->avatar_path)) {
            $this->imageStore->delete($user->avatar_path, UploadedImageType::Avatar);

            $user->avatar_path = null;
            $user->avatar_removed_at = now();
        }
    }
}
