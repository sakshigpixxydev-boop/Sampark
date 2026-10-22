<?php

namespace App\Http\Requests;

use App\Rules\SafeString;
use App\Rules\ValidPin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('user');
        
        return [
            'role_id' => ['required', 'exists:role,role_id'],
            'firstname' => ['required', 'string', 'max:100', new SafeString()],
            'middlename' => ['nullable', 'string', 'max:100', new SafeString()],
            'lastname' => ['nullable', 'string', 'max:100', new SafeString()],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sysuser', 'username')->ignore($userId, 'sysuser_id'),
                new SafeString()
            ],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'pin' => ['nullable', new ValidPin()],
            'usertype_id' => ['nullable', 'exists:usertype,usertype_id'],
            'inactive' => ['boolean'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'firstname' => strip_tags($this->firstname ?? ''),
            'middlename' => strip_tags($this->middlename ?? ''),
            'lastname' => strip_tags($this->lastname ?? ''),
            'username' => strip_tags($this->username ?? ''),
            'email' => strip_tags($this->email ?? ''),
        ]);
    }
}
