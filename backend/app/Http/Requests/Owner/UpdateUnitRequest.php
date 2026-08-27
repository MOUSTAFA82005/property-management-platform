<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
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
            'building_id'  => ['sometimes', 'required', 'integer', 'exists:buildings,id'],
            'unit_number'  => ['sometimes', 'required', 'string', 'max:255'],
            'floor'        => ['sometimes', 'required', 'integer', 'min:0', 'max:200'],
            'unit_type'    => ['sometimes', 'required', 'string', 'max:100'],
            'area'         => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'bedrooms'     => ['sometimes', 'required', 'integer', 'min:0', 'max:50'],
            'bathrooms'    => ['sometimes', 'required', 'integer', 'min:0', 'max:50'],
            'monthly_rent' => ['sometimes', 'required', 'numeric', 'min:0', 'max:99999999.99'],
            'status'       => ['sometimes', 'required', Rule::in(['available', 'occupied', 'reserved'])],
        ];
    }
}
