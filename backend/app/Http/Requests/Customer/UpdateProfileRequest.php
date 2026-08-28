<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Deliberately narrow.
     *
     * `role` and `status` are absent so they cannot be mass-assigned — a
     * customer must not be able to promote themselves to owner by adding a
     * field to the request body. The controller only ever persists the keys
     * validated here.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name'  => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes', 'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => [
                'nullable', 'string',
                'regex:/^(?:\+?20|0)?1[0125][0-9]{8}$/',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            // Optional password change; requires the current password.
            'current_password' => ['required_with:password', 'string'],
            'password'         => ['sometimes', 'required', 'confirmed', Password::min(8)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid Egyptian mobile number, for example 01012345678.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->email)) {
            $this->merge(['email' => strtolower(trim($this->email))]);
        }
    }
}
