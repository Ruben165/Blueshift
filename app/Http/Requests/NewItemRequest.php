<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewItemRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'file'  => 'required|mimes:xlsx,xls'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'File import harus diisi!',
            'file.mimes' => 'File import harus berupa file excel (xls/xlsx)!'
        ];
    }
}
