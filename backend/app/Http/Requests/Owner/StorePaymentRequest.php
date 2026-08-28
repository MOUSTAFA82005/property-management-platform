<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contract_id'     => ['required', 'integer', 'exists:contracts,id'],
            'amount'          => ['required', 'numeric', 'gt:0'],
            'due_date'        => ['required', 'date'],
            'paid_date'       => ['nullable', 'date'],
            'payment_method'  => ['nullable', 'string', 'max:255'],
            'status'          => ['required', 'in:pending,paid,overdue,cancelled'],
            'reference'       => ['nullable', 'string', 'max:255', 'unique:payments,reference'],
            'notes'           => ['nullable', 'string'],
        ];
    }
}
