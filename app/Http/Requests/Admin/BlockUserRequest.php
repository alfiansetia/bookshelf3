<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BlockUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $moderation = config('bookshelf.moderation');

        return [
            'reason' => [
                'required',
                'string',
                'min:'.$moderation['reason_min'],
                'max:'.$moderation['reason_max'],
            ],
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
            'reason.required' => 'Alasan blokir wajib diisi supaya pengguna tahu penyebabnya.',
            'reason.string' => 'Alasan blokir harus berupa teks.',
            'reason.min' => 'Alasan blokir minimal :min karakter.',
            'reason.max' => 'Alasan blokir maksimal :max karakter.',
        ];
    }
}
