<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class authStoreRequest extends FormRequest
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
            "fullname" => 'regex:/^[A-Z][a-z]+(?: [A-Z][a-z]+){0,2}$/u',
            "bio" => "string|nullable",
            "phone" => [
                "regex:/^(?:\D*\d){10,15}$/",
                "nullable"
            ],
            "notifications" => "boolean",
            "expert_mailing" => "boolean",
        ];
    }
}
