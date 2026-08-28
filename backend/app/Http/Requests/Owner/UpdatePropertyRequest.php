<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'          => ['sometimes', 'required', 'string', 'max:255'],
            'address'       => ['sometimes', 'required', 'string', 'max:2000'],
            'city'          => ['sometimes', 'required', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:5000'],
            'property_type' => ['sometimes', 'required', 'string', 'max:100'],
            'status'        => ['sometimes', 'required', Rule::in(['active', 'inactive'])],
            'is_published'  => ['sometimes', 'boolean'],
        ];
    }
}
