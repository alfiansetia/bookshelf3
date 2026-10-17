<?php

namespace App;

/**
 * Jenis gambar unggahan yang punya pengaturan sendiri di config/bookshelf.php.
 */
enum UploadedImageType: string
{
    case Cover = 'cover';
    case Avatar = 'avatar';

    /**
     * Pengaturan penyimpanan dan pengoptimalan untuk jenis unggahan ini.
     *
     * @return array{disk: string, directory: string, max_width: int<1, max>, max_height: int<1, max>, quality: int}
     */
    public function settings(): array
    {
        return [
            'disk' => config()->string("bookshelf.{$this->value}.disk"),
            'directory' => config()->string("bookshelf.{$this->value}.directory"),
            'max_width' => max(1, config()->integer("bookshelf.{$this->value}.max_width")),
            'max_height' => max(1, config()->integer("bookshelf.{$this->value}.max_height")),
            'quality' => config()->integer("bookshelf.{$this->value}.quality"),
        ];
    }
}
