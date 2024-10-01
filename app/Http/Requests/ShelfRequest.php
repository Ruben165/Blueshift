<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShelfRequest extends FormRequest
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
        $rules = [
            'name' => 'required|max:10|unique:shelfs,name',
        ];
        if($this->isMethod('patch')){
            $rules['name'] .= ','.$this->rak->id;
        }
        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Kolom nama rak harus diisi!',
            'name.max' => 'Kolom nama rak berisikan maksimal 10 huruf!',
            'name.unique' => 'Nama rak sudah digunakan!',
        ];
    }
}
