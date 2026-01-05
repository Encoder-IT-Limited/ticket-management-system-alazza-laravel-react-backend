<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('employee') ? $this->route('employee')->id : null;
        return [
            'employee_code' => 'nullable|string|unique:employees,employee_code,' . $id,
            'image' => 'nullable',
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'father_name' => 'nullable|string',
            'mother_name' => 'nullable|string',
            'email' => 'required|email|unique:employees,email,' . $id,
            'phone' => 'nullable|string|unique:employees,phone,' . $id,
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'gender' => 'nullable|string|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'blood_group' => 'nullable|string',
            'national_id' => 'nullable|string|unique:employees,national_id,' . $id,
            'passport_no' => 'nullable|string|unique:employees,passport_no,' . $id,
            'department' => 'nullable|string',
            'designation' => 'nullable|string',
            'supervisor' => 'nullable|string',
            'joining_date' => 'nullable|date',
            'employment_type' => 'nullable|string|in:full_time,part_time,contract,intern',
            'status' => 'nullable|string|in:active,inactive,terminated,resigned',
            'shift' => 'nullable|string',
            'basic_salary' => 'nullable|numeric',
            'gross_salary' => 'nullable|numeric',

            'linkedin' => 'nullable|url',
            'twitter' => 'nullable|url',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'website' => 'nullable|url',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employee_code.string' => 'The employee code must be a string.',
            'employee_code.unique' => 'The employee code has already been taken.',

            'first_name.required' => 'The first name field is required.',
            'first_name.string' => 'The first name must be a string.',

            'last_name.string' => 'The last name must be a string.',

            'father_name.string' => 'The father name must be a string.',
            'mother_name.string' => 'The mother name must be a string.',

            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',

            'phone.string' => 'The phone must be a string.',
            'phone.unique' => 'The phone has already been taken.',

            'emergency_contact_name.string' => 'The emergency contact name must be a string.',
            'emergency_contact_phone.string' => 'The emergency contact phone must be a string.',

            'present_address.string' => 'The present address must be a string.',
            'permanent_address.string' => 'The permanent address must be a string.',

            'gender.string' => 'The gender must be a string.',
            'gender.in' => 'The selected gender is invalid.',

            'date_of_birth.date' => 'The date of birth is not a valid date.',

            'blood_group.string' => 'The blood group must be a string.',

            'national_id.string' => 'The national id must be a string.',
            'national_id.unique' => 'The national id has already been taken.',

            'passport_no.string' => 'The passport number must be a string.',
            'passport_no.unique' => 'The passport number has already been taken.',

            'department.string' => 'The department must be a string.',
            'designation.string' => 'The designation must be a string.',
            'supervisor.string' => 'The supervisor must be a string.',

            'joining_date.date' => 'The joining date is not a valid date.',

            'employment_type.string' => 'The employment type must be a string.',
            'employment_type.in' => 'The selected employment type is invalid.',

            'status.string' => 'The status must be a string.',
            'status.in' => 'The selected status is invalid.',

            'shift.string' => 'The shift must be a string.',

            'basic_salary.numeric' => 'The basic salary must be a number.',
            'gross_salary.numeric' => 'The gross salary must be a number.',
        ];
    }
}
