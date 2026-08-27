<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequestRequest extends FormRequest
{
    /** Only customers raise purchase requests; the policy re-checks this. */
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    /**
     * That the unit is actually browsable, and that the customer has no open
     * request against it already, is decided in the controller where a clear
     * message can be returned.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'notes'   => ['nullable', 'string', 'max:2000'],
        ];
    }
}
