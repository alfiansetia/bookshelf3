<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menyimpan berkas gambar unggahan ke disk publik dengan nama acak.
 */
class StoreUploadedImage
{
    /**
     * Simpan gambar dan kembalikan path relatifnya di disk.
     */
    public function store(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $extension = Str::lower($file->extension() ?: $file->getClientOriginalExtension() ?: 'jpg');
        $filename = Str::uuid()->toString().'.'.$extension;
        $folder = trim($directory, '/').'/'.now()->format('Y/m');

        return (string) $file->storeAs($folder, $filename, $disk);
    }

    /**
     * Hapus gambar lama bila berkasnya masih ada.
     */
    public function delete(?string $path, string $disk = 'public'): void
    {
        if (blank($path)) {
            return;
        }

        $diskInstance = Storage::disk($disk);

        if ($diskInstance->exists($path)) {
            $diskInstance->delete($path);
        }
    }
}
