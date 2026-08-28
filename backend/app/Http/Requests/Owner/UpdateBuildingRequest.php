<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBuildingRequest extends FormRequest
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
            'property_id'  => ['sometimes', 'required', 'integer', 'exists:properties,id'],
            'name'         => ['sometimes', 'required', 'string', 'max:255'],
            'floors_count' => ['sometimes', 'required', 'integer', 'min:1', 'max:200'],
            'description'  => ['nullable', 'string', 'max:5000'],
        ];
    }
}
