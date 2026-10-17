<?php

namespace App\Http\Controllers\Onboarding;

use App\Concerns\ProfileValidationRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UsernameAvailabilityController extends Controller
{
    use ProfileValidationRules;

    /**
     * Cek ketersediaan username untuk form onboarding dan pengaturan profil.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $username = $this->normalizeUsernameValue($request->query('username'));

        $validator = validator(
            ['username' => $username],
            ['username' => $this->usernameRules($request->user()?->id)],
            $this->profileMessages(),
        );

        if (! $validator->fails()) {
            return $this->available($username);
        }

        $message = $validator->errors()->first('username');

        // Username yang sedang dipakai sendiri dianggap tersedia.
        if ($message !== '' && $this->isOwnUsername($request, $username)) {
            $message = '';
        }

        return response()->json([
            'available' => $message === '',
            'username' => $username,
            'message' => $message === '' ? 'Username ini bisa kamu pakai.' : $message,
        ]);
    }

    /**
     * Jawaban untuk username yang lolos validasi.
     */
    private function available(?string $username): JsonResponse
    {
        return response()->json([
            'available' => true,
            'username' => Str::lower((string) $username),
            'message' => 'Username ini bisa kamu pakai.',
        ]);
    }

    /**
     * Apakah username ini milik pengguna yang memeriksa dan bukan kata terlarang.
     */
    private function isOwnUsername(Request $request, ?string $username): bool
    {
        if ($username === null || $request->user()?->username !== $username) {
            return false;
        }

        return ! in_array($username, config()->array('bookshelf.username.reserved'), true);
    }
}
