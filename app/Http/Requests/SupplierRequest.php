<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
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
        $supplierCode = $this->input('supplier_code');
        
        $rules = [
            'name' => 'required|sometimes',
            'npwp' => 'required|max:25|sometimes',
            'email' => 'required|email|max:50|sometimes',
            'phone' => 'required|max:20|sometimes',
            'address' => 'required|sometimes',
        ];

        $rules['supplier_code'] = ['required', 'max:25', 'sometimes',
            Rule::unique('suppliers')->ignore($supplierCode, 'supplier_code')
        ];

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
            'supplier_code.required' => 'Kolom kode supplier harus diisi!',
            'supplier_code.max' => 'Kolom kode supplier berisikan maksimal 25 huruf!',
            'supplier_code.unique' => 'Kode supplier sudah digunakan!',
            'name.required' => 'Kolom nama supplier harus diisi!',
            'npwp.required' => 'Kolom NPWP supplier harus diisi!',
            'npwp.max' => 'Kolom NPWP supplier berisikan maksimal 25 huruf!',
            'email.required' => 'Kolom email supplier harus diisi!',
            'email.max' => 'Kolom email supplier berisikan maksimal 50 huruf!',
            'phone.required' => 'Kolom phone nomor telephone supplierss harus diisi!',
            'phone.max' => 'Kolom nomor telephone supplier berisikan maksimal 20 angka!',
            'address.required' => 'Kolom alamat supplier harus diisi!',
        ];
    }
}
