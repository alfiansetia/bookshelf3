<?php

namespace App\Http\Requests;

use App\ShelfTheme;
use App\ShelfVisibility;
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
     * Terima nilai lama is_public agar kompatibel dengan form lama.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('visibility') && $this->has('is_public')) {
            $this->merge([
                'visibility' => $this->boolean('is_public')
                    ? ShelfVisibility::Public->value
                    : ShelfVisibility::Private->value,
            ]);
        }
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
            'visibility' => ['required', Rule::enum(ShelfVisibility::class)],
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
