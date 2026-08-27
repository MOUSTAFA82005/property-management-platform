<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Registration is public — anyone may create an account.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The roles a self-service registration is allowed to ask for.
     *
     * The project defines exactly two roles, but only `customer` is
     * self-service: an owner account grants portfolio-wide management
     * rights, so letting anyone claim it from a public form would be a
     * privilege-escalation hole. Flip ALLOW_OWNER_REGISTRATION in .env
     * if owners should be able to sign themselves up.
     *
     * @return array<int, string>
     */
    public static function allowedRoles(): array
    {
        return config('auth.allow_owner_registration', false)
            ? ['customer', 'owner']
            : ['customer'];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],

            // Nullable to match the schema. Validated as an Egyptian mobile
            // number when supplied, which is what the platform targets.
            'phone' => [
                'nullable',
                'string',
                'regex:/^(?:\+?20|0)?1[0125][0-9]{8}$/',
                Rule::unique('users', 'phone'),
            ],

            'password' => ['required', 'confirmed', Password::min(8)],

            'role' => ['nullable', 'string', Rule::in(self::allowedRoles())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex'  => 'Enter a valid Egyptian mobile number, for example 01012345678.',
            'phone.unique' => 'This phone number is already registered.',
            'role.in'      => 'You cannot register with that account type.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->email)) {
            $this->merge(['email' => strtolower(trim($this->email))]);
        }
    }

    /**
     * The role to persist. Never read straight from the request — the
     * whitelist above is what makes this safe.
     */
    public function resolvedRole(): string
    {
        $role = $this->input('role');

        return in_array($role, self::allowedRoles(), true) ? $role : 'customer';
    }
}
