<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSocialLinksRequest;
use App\SocialPlatform;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SocialLinksController extends Controller
{
    /**
     * Halaman pengaturan tautan sosial.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('settings/Socials', [
            'platforms' => SocialPlatform::options(),
            'links' => collect(SocialPlatform::cases())
                ->mapWithKeys(fn (SocialPlatform $platform): array => [
                    $platform->column() => $user->getAttribute($platform->column()),
                ])
                ->all(),
            'profiles' => $user->socialProfiles(),
            'shelfUrl' => $user->hasUsername() ? route('shelf.show', $user->username) : null,
        ]);
    }

    /**
     * Simpan tautan sosial milik pengguna.
     */
    public function update(UpdateSocialLinksRequest $request): RedirectResponse
    {
        $user = $request->user();

        $attributes = collect($request->validated())
            ->map(fn ($value): ?string => is_string($value) && trim($value) !== '' ? trim($value) : null)
            ->all();

        $user->forceFill($attributes)->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Tautan sosial kamu sudah disimpan.',
        ]);

        return to_route('socials.edit');
    }
}
