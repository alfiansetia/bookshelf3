<?php

namespace App\Concerns;

use App\BookStatus;
use App\Validation\NoProfanity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait BookValidationRules
{
    /**
     * Normalisasi warna sampul: string kosong dianggap tidak memilih warna.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('cover_color') === '') {
            $this->merge(['cover_color' => null]);
        }
    }

    /**
     * Aturan validasi buku untuk halaman tambah maupun ubah.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function bookRules(): array
    {
        $limits = config('bookshelf.limits');
        $cover = config('bookshelf.cover');
        $rating = config('bookshelf.rating');
        $languages = array_keys(config('bookshelf.languages'));

        return [
            'title' => ['required', 'string', 'max:255', new NoProfanity],
            'author' => ['required', 'string', 'max:255', new NoProfanity],
            'publisher' => ['nullable', 'string', 'max:255', new NoProfanity],
            'published_year' => ['nullable', 'integer', 'min:1000', 'max:'.(date('Y') + 1)],
            'language' => ['required', 'string', Rule::in($languages)],
            'status' => ['required', Rule::enum(BookStatus::class)],
            'rating' => ['nullable', 'integer', 'min:'.$rating['min'], 'max:'.$rating['max']],
            'synopsis' => ['nullable', 'string', 'max:'.$limits['synopsis'], new NoProfanity],
            'review' => ['nullable', 'string', 'max:'.$limits['review'], new NoProfanity],
            'cover_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'total_pages' => ['nullable', 'integer', 'min:1', 'max:20000'],
            'started_at' => ['nullable', 'date', 'before_or_equal:today'],
            'finished_at' => ['nullable', 'date', 'after_or_equal:started_at', 'before_or_equal:today'],
            'is_favorite' => ['boolean'],
            'remove_cover' => ['boolean'],
            'cover' => [
                'nullable',
                'file',
                'image',
                'mimes:'.implode(',', $cover['mimes']),
                'max:'.$cover['max_kilobytes'],
                Rule::dimensions()
                    ->maxWidth($cover['max_source_width'])
                    ->maxHeight($cover['max_source_height']),
            ],
        ];
    }

    /**
     * Pesan validasi berbahasa Indonesia untuk form buku.
     *
     * @return array<string, string>
     */
    protected function bookMessages(): array
    {
        $cover = config('bookshelf.cover');
        $languages = implode(', ', array_values(config('bookshelf.languages')));

        return [
            'title.required' => 'Judul buku wajib diisi.',
            'author.required' => 'Nama penulis wajib diisi.',
            'language.required' => 'Bahasa buku wajib dipilih.',
            'language.in' => 'Bahasa yang dipilih belum didukung. Pilihan yang tersedia: '.$languages.'.',
            'status.required' => 'Status baca wajib dipilih.',
            'status.enum' => 'Status baca tidak dikenali.',
            'rating.between' => 'Rating harus antara 1 sampai 5 bintang.',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
            'cover.image' => 'Sampul harus berupa gambar.',
            'cover.mimes' => 'Format sampul harus JPG, PNG, atau WEBP.',
            'cover.max' => 'Ukuran sampul maksimal :max kilobita.',
            'cover.dimensions' => sprintf(
                'Sampul terlalu besar. Maksimal %d x %d piksel.',
                $cover['max_source_width'],
                $cover['max_source_height'],
            ),
            'cover_color.regex' => 'Warna sampul harus kode hex seperti #b4763a.',
            'total_pages.min' => 'Jumlah halaman minimal 1.',
            'started_at.before_or_equal' => 'Tanggal mulai tidak boleh melewati hari ini.',
            'finished_at.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
            'finished_at.before_or_equal' => 'Tanggal selesai tidak boleh melewati hari ini.',
        ];
    }
}
