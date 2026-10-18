<?php

namespace App\Http\Controllers\Auth;

use App\Actions\ImportRemoteAvatar;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleAuthController extends Controller
{
    public function __construct(private ImportRemoteAvatar $importRemoteAvatar) {}

    /**
     * Kirim pengguna ke halaman persetujuan Google.
     */
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Tangani pengembalian dari Google lalu login pengguna.
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Login Google dibatalkan.',
            ]);

            return to_route('login');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Gagal login dengan Google. Silakan coba lagi.',
            ]);

            return to_route('login');
        }

        $user = $this->findOrCreateUser($googleUser);

        if ($user->isBlocked()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Akun kamu diblokir: '.$user->blockedMessage(),
            ]);

            return to_route('login');
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        if (! $user->hasUsername()) {
            Inertia::flash('toast', [
                'type' => 'info',
                'message' => 'Satu langkah lagi: pilih username untuk rak kamu.',
            ]);

            return to_route('onboarding.username.edit');
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Selamat datang kembali, '.$user->name.'!',
        ]);

        return to_route('dashboard');
    }

    /**
     * Temukan pengguna lama (berdasarkan Google ID atau email) atau buat baru.
     */
    private function findOrCreateUser(SocialiteUser $googleUser): User
    {
        $email = $googleUser->getEmail() ?? $googleUser->getId().'@user.google';
        $googleId = (string) $googleUser->getId();

        $user = User::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if ($user !== null) {
            $user->forceFill([
                'google_id' => $googleId,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();

            if (blank($user->avatar_path)) {
                $this->importRemoteAvatar->handle($user, $googleUser->getAvatar());
            }

            return $user;
        }

        $user = User::create([
            'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Pembaca Baru',
            'email' => $email,
            'google_id' => $googleId,
        ]);

        // Email dari Google sudah terbukti milik pengguna ini.
        $user->forceFill(['email_verified_at' => now()])->save();

        $this->importRemoteAvatar->handle($user, $googleUser->getAvatar());

        return $user;
    }
}
