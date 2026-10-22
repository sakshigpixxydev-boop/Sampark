<?php

namespace App\Http\Requests;

use App\Rules\SafeString;
use App\Rules\ValidPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
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
        $studentId = $this->route('student');
        
        return [
            'studenttype_id' => ['required', 'exists:studenttype,studenttype_id'],
            'eduboard_id' => ['required', 'exists:ms_eductionalboard,eduboard_id'],
            'standard_id' => ['required', 'exists:ms_standard,standard_id'],
            'takula_id' => ['required', 'exists:ms_taluko,takula_id'],
            'firstname' => ['required', 'string', 'max:100', new SafeString()],
            'middlename' => ['required', 'string', 'max:100', new SafeString()],
            'lastname' => ['required', 'string', 'max:100', new SafeString()],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('student', 'email')->ignore($studentId, 'student_id'),
            ],
            'gender' => ['nullable', 'in:M,F,O'],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:500', new SafeString()],
            'contact1' => ['nullable', new ValidPhoneNumber()],
            'contact2' => ['nullable', new ValidPhoneNumber()],
            'school_id' => ['nullable', 'exists:school,school_id'],
            'inactive' => ['boolean'],
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
            'email' => strip_tags($this->email ?? ''),
            'address' => strip_tags($this->address ?? ''),
        ]);
    }
}
