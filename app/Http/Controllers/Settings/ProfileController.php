<?php

namespace App\Http\Controllers\Settings;

use App\Actions\StoreUploadedImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
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
        $attributes = $request->safe()->except('avatar');

        if ($request->hasFile('avatar')) {
            $this->imageStore->delete($user->avatar_path, (string) config('bookshelf.avatar.disk'));

            $attributes['avatar_path'] = $this->imageStore->store(
                $request->file('avatar'),
                (string) config('bookshelf.avatar.directory'),
                (string) config('bookshelf.avatar.disk'),
            );
        }

        $user->fill($attributes);

        $user->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Profil kamu sudah diperbarui.',
        ]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
