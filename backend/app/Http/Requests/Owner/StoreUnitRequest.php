<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
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
            'building_id'  => ['required', 'integer', 'exists:buildings,id'],
            'unit_number'  => ['required', 'string', 'max:255'],
            'floor'        => ['nullable', 'integer', 'min:0', 'max:200'],
            'unit_type'    => ['required', 'string', 'max:100'],
            'area'         => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'bedrooms'     => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms'    => ['nullable', 'integer', 'min:0', 'max:50'],
            'monthly_rent' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            // The schema supports exactly these three. There is no `sold`.
            'status'       => ['nullable', Rule::in(['available', 'occupied', 'reserved'])],
        ];
    }
}
