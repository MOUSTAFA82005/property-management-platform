<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StoreBuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The property must exist; that it belongs to the authenticated owner is
     * checked in the controller so the caller gets a 403 rather than a 422.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'property_id'  => ['required', 'integer', 'exists:properties,id'],
            'name'         => ['required', 'string', 'max:255'],
            'floors_count' => ['nullable', 'integer', 'min:1', 'max:200'],
            'description'  => ['nullable', 'string', 'max:5000'],
        ];
    }
}
