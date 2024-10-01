<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ZoneRequest extends FormRequest
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
            'name' => 'required|max:50|unique:zones,name',
        ];
        if($this->isMethod('patch')){
            $rules['name'] .= ','.$this->wilayah->id;
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
            'name.required' => 'Kolom nama wilayah harus diisi!',
            'name.max' => 'Kolom nama wilayah berisikan maksimal 50 huruf!',
            'name.unique' => 'Nama wilayah sudah digunakan!',
        ];
    }
}
