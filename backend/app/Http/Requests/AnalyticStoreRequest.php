<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyticStoreRequest extends FormRequest
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
            "title" => "required|string",
            "description" => "required|string",
            "fields" => "required|array|min:1",
            "image" => "required|image|mimes:jpeg,png,jpg,gif,svg",
            "link" => "nullable|string",
            'pdf' => 'nullable|file|mimetypes:application/pdf,application/octet-stream',
        ];
    }
}
