<?php

namespace App;

/**
 * Status baca sebuah buku pada rak pengguna.
 */
enum BookStatus: string
{
    case WantToRead = 'want_to_read';
    case Reading = 'reading';
    case Finished = 'finished';
    case OnHold = 'on_hold';
    case Dropped = 'dropped';

    /**
     * Label Bahasa Indonesia untuk status ini.
     */
    public function label(): string
    {
        return match ($this) {
            self::WantToRead => 'Ingin Dibaca',
            self::Reading => 'Sedang Dibaca',
            self::Finished => 'Sudah Dibaca',
            self::OnHold => 'Ditunda',
            self::Dropped => 'Berhenti Dibaca',
        };
    }

    /**
     * Kalimat pendek yang menjelaskan status ini.
     */
    public function description(): string
    {
        return match ($this) {
            self::WantToRead => 'Masuk daftar buruan, belum mulai dibaca.',
            self::Reading => 'Sedang kamu baca saat ini.',
            self::Finished => 'Sudah tamat dan masuk koleksi.',
            self::OnHold => 'Berhenti dulu, mungkin dilanjut nanti.',
            self::Dropped => 'Sudah tidak dilanjutkan.',
        };
    }

    /**
     * Nama ikon Lucide yang dipakai di frontend.
     */
    public function icon(): string
    {
        return match ($this) {
            self::WantToRead => 'Bookmark',
            self::Reading => 'BookOpen',
            self::Finished => 'BookCheck',
            self::OnHold => 'Pause',
            self::Dropped => 'BookX',
        };
    }

    /**
     * Kunci warna semantik yang dipetakan ke kelas Tailwind di frontend.
     */
    public function color(): string
    {
        return match ($this) {
            self::WantToRead => 'amber',
            self::Reading => 'sky',
            self::Finished => 'emerald',
            self::OnHold => 'violet',
            self::Dropped => 'rose',
        };
    }

    /**
     * Status yang menandakan buku sudah selesai dibaca.
     */
    public function isFinished(): bool
    {
        return $this === self::Finished;
    }

    /**
     * Status default untuk buku baru.
     */
    public static function default(): self
    {
        return self::WantToRead;
    }

    /**
     * Urutan tampilan status dari yang paling menggambarkan rak.
     *
     * @return array<int, self>
     */
    public static function ordered(): array
    {
        return [self::Reading, self::Finished, self::WantToRead, self::OnHold, self::Dropped];
    }

    /**
     * Semua status dalam bentuk array yang siap dikirim ke frontend.
     *
     * @return array<int, array{value: string, label: string, description: string, icon: string, color: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $status): array => [
            'value' => $status->value,
            'label' => $status->label(),
            'description' => $status->description(),
            'icon' => $status->icon(),
            'color' => $status->color(),
        ], self::ordered());
    }

    /**
     * Pilihan status untuk dropdown filter (label + value saja).
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (self::cases() as $status) {
            $labels[$status->value] = $status->label();
        }

        return $labels;
    }
}
