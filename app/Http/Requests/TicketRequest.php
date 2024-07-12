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
            'title' => 'required|string',
            'description' => 'sometimes|required|string',
        ];
        if ($this->isMethod('put')) {
            $rule['status'] = 'sometimes|required|boolean';
        }
        return $rule;
    }
}
