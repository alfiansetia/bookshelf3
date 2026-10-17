<?php

namespace App\Actions;

use App\UploadedImageType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Menyimpan gambar unggahan sebagai WebP yang sudah diperkecil.
 *
 * Gambar diubah orientasinya mengikuti EXIF, diperkecil agar tidak melebihi
 * batas pada config/bookshelf.php, lalu dikompres sebelum disimpan.
 */
class StoreUploadedImage
{
    /**
     * Batas memori selama gambar diproses, karena GD memuat gambar penuh ke memori.
     */
    private const PROCESSING_MEMORY_LIMIT = '512M';

    /**
     * Simpan gambar dan kembalikan path relatifnya di disk.
     */
    public function store(UploadedFile $file, UploadedImageType $type): string
    {
        $settings = $type->settings();
        $folder = trim($settings['directory'], '/').'/'.now()->format('Y/m');
        $previousLimit = ini_get('memory_limit');

        ini_set('memory_limit', self::PROCESSING_MEMORY_LIMIT);

        try {
            // Sengaja tanpa `storePublicly()`: R2 mengabaikan ACL, dan bucket S3
            // modern menggagalkan permintaan yang membawa `x-amz-acl`. Akses
            // publik diatur di sisi bucket (domain r2.dev atau domain sendiri).
            $path = Image::fromUpload($file)
                ->orient()
                ->scale(width: $settings['max_width'], height: $settings['max_height'])
                ->optimize(quality: $settings['quality'])
                ->store(path: $folder, disk: $settings['disk']);
        } finally {
            ini_set('memory_limit', $previousLimit);
        }

        if ($path === false) {
            throw new RuntimeException('Gambar gagal disimpan ke penyimpanan.');
        }

        return $path;
    }

    /**
     * Hapus gambar lama bila berkasnya masih ada.
     */
    public function delete(?string $path, UploadedImageType $type): void
    {
        if (blank($path)) {
            return;
        }

        $disk = Storage::disk($type->settings()['disk']);

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}
