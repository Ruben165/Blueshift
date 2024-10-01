<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
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
            'name' => 'required|max:255|unique:roles,name',
            'permissions' => 'required|array|min:1'
        ];
        if($this->isMethod('patch')){
            $rules['name'] .= ','.$this->role->id;
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
            'name.required' => 'Kolom nama harus diisi!',
            'name.max' => 'Kolom nama berisikan maksimal 255 huruf!',
            'name.unique' => 'Nama sudah digunakan!',
            'permissions.required' => 'Role harus memiliki minimal 1 permission!',
            'permissions.min' => 'Role harus memiliki minimal 1 permission!',
        ];
    }
}
