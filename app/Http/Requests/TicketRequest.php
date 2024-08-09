<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // if request method is post, then only client can create ticket
        if ($this->isMethod('post')) {
            return auth()->user()->role !== 'admin';
        }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rule = [
            'title' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'admin_id' => 'sometimes|required|exists:users,id',
            'files' => 'sometimes|required|array',
            'files.*' => 'sometimes|required|file|mimes:jpg,jpeg,png,pdf,docx,doc|max:4096',
        ];
        if ($this->isMethod('put') && (auth()->user()->role === 'admin')) {
            $rule['is_resolved'] = 'sometimes|required|boolean';
        }
        return $rule;
    }
}
