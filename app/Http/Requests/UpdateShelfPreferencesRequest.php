<?php

namespace App\Http\Requests;

use App\ShelfTheme;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShelfPreferencesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Pastikan nilai boolean terkirim sebagai boolean sungguhan.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_public' => $this->boolean('is_public'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'theme' => ['required', Rule::enum(ShelfTheme::class)],
            'is_public' => ['boolean'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'theme.required' => 'Gaya rak wajib dipilih.',
            'theme.enum' => 'Gaya rak tidak dikenali.',
        ];
    }
}
