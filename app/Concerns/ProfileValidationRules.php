<?php

namespace App\Concerns;

use App\Models\User;
use App\Validation\NoProfanity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        $limits = config('bookshelf.limits');
        $avatar = config('bookshelf.avatar');

        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
            'username' => $this->usernameRules($userId),
            'tagline' => ['nullable', 'string', 'max:'.$limits['tagline'], new NoProfanity],
            'bio' => ['nullable', 'string', 'max:'.$limits['bio'], new NoProfanity],
            'remove_avatar' => ['boolean'],
            'avatar' => [
                'nullable',
                'file',
                'image',
                'mimes:'.implode(',', $avatar['mimes']),
                'max:'.$avatar['max_kilobytes'],
                Rule::dimensions()
                    ->maxWidth($avatar['max_source_width'])
                    ->maxHeight($avatar['max_source_height']),
            ],
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255', new NoProfanity];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    /**
     * Aturan username publik yang dipakai sebagai alamat rak.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function usernameRules(?int $userId = null): array
    {
        $username = config('bookshelf.username');

        return [
            'required',
            'string',
            'min:'.$username['min'],
            'max:'.$username['max'],
            'regex:'.$username['regex'],
            Rule::notIn($username['reserved']),
            new NoProfanity(matchSubstring: true),
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    /**
     * Rapikan username menjadi huruf kecil tanpa spasi.
     */
    protected function normalizeUsernameValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Str::of($value)->trim()->lower()->replace(' ', '-')->value();
    }

    /**
     * Pesan validasi berbahasa Indonesia untuk field profil.
     *
     * @return array<string, string>
     */
    protected function profileMessages(): array
    {
        $avatar = config('bookshelf.avatar');

        return [
            'username.required' => 'Username wajib diisi karena dipakai sebagai alamat rak kamu.',
            'username.unique' => 'Username ini sudah dipakai orang lain.',
            'username.regex' => 'Username hanya boleh berisi huruf kecil, angka, titik, garis bawah, dan tanda hubung.',
            'username.not_in' => 'Username ini tidak boleh dipakai, silakan pilih yang lain.',
            'username.min' => 'Username minimal :min karakter.',
            'username.max' => 'Username maksimal :max karakter.',
            'avatar.image' => 'Berkas foto harus berupa gambar.',
            'avatar.max' => 'Ukuran foto maksimal :max kilobita.',
            'avatar.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'avatar.dimensions' => sprintf(
                'Foto terlalu besar. Maksimal %d x %d piksel.',
                $avatar['max_source_width'],
                $avatar['max_source_height'],
            ),
        ];
    }
}
