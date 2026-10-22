<?php

namespace App\Http\Requests;

use App\Rules\SafeString;
use App\Rules\ValidPin;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'role_id' => ['required', 'exists:role,role_id'],
            'firstname' => ['required', 'string', 'max:100', new SafeString()],
            'middlename' => ['nullable', 'string', 'max:100', new SafeString()],
            'lastname' => ['nullable', 'string', 'max:100', new SafeString()],
            'username' => ['required', 'string', 'max:255', 'unique:sysuser,username', new SafeString()],
            'password' => ['required_without:pin', 'nullable', 'string', 'min:8', 'max:255'],
            'pin' => ['required_without:password', 'nullable', new ValidPin()],
            'usertype_id' => ['nullable', 'exists:usertype,usertype_id'],
            'inactive' => ['boolean'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'password.required_without' => 'Either password or PIN is required.',
            'pin.required_without' => 'Either password or PIN is required.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize inputs
        $this->merge([
            'firstname' => strip_tags($this->firstname ?? ''),
            'middlename' => strip_tags($this->middlename ?? ''),
            'lastname' => strip_tags($this->lastname ?? ''),
            'username' => strip_tags($this->username ?? ''),
            'email' => strip_tags($this->email ?? ''),
        ]);
    }
}
