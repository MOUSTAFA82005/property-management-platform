<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyRequest extends FormRequest
{
    /** Authorization is handled by PropertyPolicy in the controller. */
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
            'name'          => ['required', 'string', 'max:255'],
            'address'       => ['required', 'string', 'max:2000'],
            'city'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:5000'],
            'property_type' => ['required', 'string', 'max:100'],
            'status'        => ['nullable', Rule::in(['active', 'inactive'])],
            'is_published'  => ['nullable', 'boolean'],
        ];
    }
}
