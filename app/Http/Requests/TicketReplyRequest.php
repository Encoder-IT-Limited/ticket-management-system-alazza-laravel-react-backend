<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketReplyRequest extends FormRequest
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
            'message' => ['required', 'string'],
            'attachments' => 'sometimes|nullable|array',
            'attachments.*' => 'sometimes|nullable|file|mimes:docx,pdf,jpeg,png,jpg,txt,svg,zip,gif|max:10240',

        ];
    }
}
