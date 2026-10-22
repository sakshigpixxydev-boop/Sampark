<?php

namespace App\Http\Requests;

use App\Rules\SafeString;
use App\Rules\SecureImageFile;
use App\Rules\ValidPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolRequest extends FormRequest
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
        return [
            'schoolname' => ['required', 'string', 'max:1000', new SafeString()],
            'schooltype_id' => ['required', 'exists:schooltype,schooltype_id'],
            'eduboard_id' => ['required', 'exists:ms_eductionalboard,eduboard_id'],
            'adminname' => ['required', 'string', 'max:200', new SafeString()],
            'admincontact' => ['required', new ValidPhoneNumber()],
            'takula_id' => ['nullable', 'exists:ms_taluko,takula_id'],
            'address' => ['nullable', 'string', 'max:500', new SafeString()],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', new ValidPhoneNumber()],
            'logo' => ['nullable', 'file', new SecureImageFile()],
            'inactive' => ['boolean'],
            'status' => ['in:0,1'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'schoolname' => strip_tags($this->schoolname ?? ''),
            'adminname' => strip_tags($this->adminname ?? ''),
            'address' => strip_tags($this->address ?? ''),
            'email' => strip_tags($this->email ?? ''),
        ]);
    }
}
