<?php

namespace App\Http\Requests;

use App\SocialPlatform;
use App\Validation\NoProfanity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialLinksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Rapikan nilai tiap platform sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (SocialPlatform::cases() as $platform) {
            $column = $platform->column();
            $value = $this->input($column);

            if (! is_string($value)) {
                continue;
            }

            $normalized[$column] = $platform->normalizeValue($value);
        }

        $this->merge($normalized);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [];

        foreach (SocialPlatform::cases() as $platform) {
            $rules[$platform->column()] = $platform->isUsernameOnly()
                ? ['nullable', 'string', 'max:'.$platform->maxLength(), 'regex:/^[A-Za-z0-9._-]+$/', new NoProfanity(matchSubstring: true)]
                : ['nullable', 'string', 'max:'.$platform->maxLength(), 'url'];
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [];

        foreach (SocialPlatform::cases() as $platform) {
            $messages[$platform->column().'.regex'] = 'Username '.$platform->label().' hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.';
            $messages[$platform->column().'.url'] = 'Alamat '.$platform->label().' harus berupa tautan yang valid.';
            $messages[$platform->column().'.max'] = 'Isian '.$platform->label().' terlalu panjang.';
        }

        return $messages;
    }
}
