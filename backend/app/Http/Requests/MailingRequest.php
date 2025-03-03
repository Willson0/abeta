<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MailingRequest extends FormRequest
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
            "fullname" => "string|min:3",
            "username" => "string|min:3",
            "telegram_id" => "numeric|exists:users,telegram_id",
            "webinar_id" => "numeric|exists:webinars,id",
            "ids" => "array",
            "ids.*" => "numeric|exists:users,id",
            "text" => "string|min:3|required",
        ];
    }
}
