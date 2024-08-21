<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'role' => 'required|string|in:admin,staff,client',
            'company' => 'sometimes|required',
            'section' => 'sometimes|required',
            'position' => 'sometimes|required',

            'user_id_documents' => 'sometimes|nullable|array',
            'user_id_documents.*' => 'sometimes|nullable|file|mimes:pdf,jpeg,png,jpg,txt,svg,zip,gif|max:10240',

            'device_licenses' => 'sometimes|nullable|array',
            'device_licenses.*' => 'sometimes|nullable|file|mimes:pdf,jpeg,png,jpg,txt,svg,zip,gif|max:10240',

            'other_documents' => 'sometimes|nullable|array',
            'other_documents.*' => 'sometimes|nullable|file|mimes:pdf,jpeg,png,jpg,txt,svg,zip,gif|max:10240',
        ];
    }
}
