<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MailingSendRequest extends FormRequest
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
            "users" => "nullable|array",
            "users.*" => "integer|exists:users,id",
            "webinars" => "nullable|array",
            "webinars.*" => "integer|exists:webinars,id",
            "analytics" => "nullable|array",
            "analytics.*" => "integer|exists:analytics,id",
            "text" => "string|required",
            "image" => "nullable|image|mimes:jpeg,png,jpg,gif,svg",
        ];
    }
}
