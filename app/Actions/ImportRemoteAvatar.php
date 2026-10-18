<?php

namespace App\Actions;

use App\Models\User;
use App\UploadedImageType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Mengunduh foto profil Google agar tidak bergantung pada URL pihak ketiga.
 *
 * Gambar yang diunduh dilewatkan pipeline StoreUploadedImage supaya hasilnya
 * sama dengan avatar unggahan: diperkecil, dikompres jadi WebP, dan disimpan
 * di disk gambar yang sedang dikonfigurasi.
 */
class ImportRemoteAvatar
{
    /**
     * Jenis gambar yang boleh diimpor.
     *
     * @var array<int, string>
     */
    private const ALLOWED_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
    ];

    /**
     * Maksimal ukuran berkas avatar yang diimpor (dalam byte).
     */
    private const MAX_BYTES = 2_097_152;

    public function __construct(private StoreUploadedImage $imageStore) {}

    /**
     * Impor foto Google bila pengguna belum punya foto dan belum memilih
     * "tanpa foto".
     */
    public function handle(User $user, ?string $url): bool
    {
        if (blank($url) || filled($user->avatar_path) || filled($user->avatar_removed_at)) {
            return false;
        }

        $body = $this->download($url);

        if ($body === null) {
            return false;
        }

        try {
            $path = $this->imageStore->storeContents($body, UploadedImageType::Avatar);
        } catch (Throwable) {
            return false;
        }

        $user->forceFill(['avatar_path' => $path])->save();

        return true;
    }

    /**
     * Unduh avatar berukuran besar, lalu jatuh ke URL asli bila gagal.
     */
    private function download(string $url): ?string
    {
        foreach ($this->downloadUrls($url) as $candidate) {
            $body = $this->fetch($candidate);

            if ($body !== null) {
                return $body;
            }
        }

        return null;
    }

    /**
     * URL yang dicoba berurutan: ukuran besar dulu, lalu URL asli dari Google.
     *
     * @return array<int, string>
     */
    private function downloadUrls(string $url): array
    {
        return array_values(array_unique([$this->withLargeSize($url), $url]));
    }

    /**
     * Ganti penanda ukuran foto Google (misalnya `=s96-c`) jadi sesuai batas avatar.
     *
     * Foto default dari Google hanya 96 piksel sehingga pecah saat ditampilkan,
     * sedangkan parameter ukurannya bisa diminta ulang.
     */
    private function withLargeSize(string $url): string
    {
        if (! str_contains($url, 'googleusercontent.com')) {
            return $url;
        }

        $size = config()->integer('bookshelf.avatar.max_width');

        return (string) preg_replace('/=s\d+(-c)?$/', '=s'.$size.'-c', $url);
    }

    /**
     * Ambil isi gambar dari satu URL bila jenis dan ukurannya didukung.
     */
    private function fetch(string $url): ?string
    {
        try {
            $response = Http::timeout(10)->get($url);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $contentType = Str::of((string) $response->header('Content-Type'))->before(';')->trim()->lower()->value();

        if (! in_array($contentType, self::ALLOWED_TYPES, true)) {
            return null;
        }

        $body = $response->body();

        if ($body === '' || strlen($body) > self::MAX_BYTES) {
            return null;
        }

        return $body;
    }
}
