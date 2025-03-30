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
            "fullname" => 'regex:/^[А-ЯЁ][а-яё]+(?: [А-ЯЁ][а-яё]+){0,2}$/u',
            "bio" => "",
            "phone" => [
                "regex:/^\+?[1-9]\d{1,14}$/"
            ],
            "notifications" => "boolean",
            "expert_mailing" => "boolean",
        ];
    }
}
