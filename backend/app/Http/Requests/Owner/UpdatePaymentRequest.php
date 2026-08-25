<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $paymentId = $this->route('payment')?->id;

        return [
            'contract_id'     => ['sometimes', 'required', 'integer', 'exists:contracts,id'],
            'amount'          => ['sometimes', 'required', 'numeric', 'gt:0'],
            'due_date'        => ['sometimes', 'required', 'date'],
            'paid_date'       => ['nullable', 'date'],
            'payment_method'  => ['nullable', 'string', 'max:255'],
            'status'          => ['sometimes', 'required', 'in:pending,paid,overdue,cancelled'],
            'reference'       => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('payments', 'reference')->ignore($paymentId),
            ],
            'notes'           => ['nullable', 'string'],
        ];
    }
}
