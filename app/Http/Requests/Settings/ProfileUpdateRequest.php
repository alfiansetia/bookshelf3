<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Rapikan username sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => $this->normalizeUsernameValue($this->input('username')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->profileRules($this->user()?->id);
    }

    /**
     * Batasi penggantian username agar alamat rak tidak berpindah terlalu sering.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $user = $this->user();

                // Lewati bila field lain sudah gagal supaya pesannya tidak menumpuk.
                if ($user === null || $validator->errors()->has('username')) {
                    return;
                }

                if ($this->input('username') === $user->username) {
                    return;
                }

                $availableAt = $user->usernameChangeAvailableAt();

                if ($availableAt === null) {
                    return;
                }

                $validator->errors()->add('username', sprintf(
                    'Username hanya bisa diganti sekali setiap %d hari. Kamu bisa ganti lagi pada %s.',
                    (int) config('bookshelf.username.cooldown_days'),
                    $availableAt->translatedFormat('j F Y, H:i'),
                ));
            },
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->profileMessages();
    }
}
