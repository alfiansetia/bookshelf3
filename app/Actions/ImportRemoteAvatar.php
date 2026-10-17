<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Mengunduh foto profil Google agar tidak bergantung pada URL pihak ketiga.
 */
class ImportRemoteAvatar
{
    /**
     * Jenis gambar yang boleh diimpor beserta ekstensinya.
     *
     * @var array<string, string>
     */
    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Maksimal ukuran avatar yang diimpor (dalam byte).
     */
    private const MAX_BYTES = 2_097_152;

    /**
     * Unduh avatar dari URL dan simpan sebagai avatar lokal pengguna.
     */
    public function handle(User $user, ?string $url): bool
    {
        if (blank($url)) {
            return false;
        }

        try {
            $response = Http::timeout(10)->get($url);
        } catch (Throwable) {
            return false;
        }

        if (! $response->successful()) {
            return false;
        }

        $contentType = Str::of((string) $response->header('Content-Type'))->before(';')->trim()->lower()->value();
        $extension = self::ALLOWED_TYPES[$contentType] ?? null;

        if ($extension === null) {
            return false;
        }

        $body = $response->body();

        if ($body === '' || strlen($body) > self::MAX_BYTES) {
            return false;
        }

        $disk = config('bookshelf.avatar.disk');
        $path = trim((string) config('bookshelf.avatar.directory'), '/').'/google/'.Str::uuid()->toString().'.'.$extension;

        Storage::disk($disk)->put($path, $body);

        $user->forceFill(['avatar_path' => $path])->save();

        return true;
    }
}
