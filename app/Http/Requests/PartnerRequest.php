<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartnerRequest extends FormRequest
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
        $clinicId = $this->input('clinic_id');

        $rules = [
            'clinic_id' => 'required|max:50|unique:partners,clinic_id|sometimes',
            'name' => 'required|sometimes',
            'email' => 'required|email|max:50|sometimes',
            'phone' => 'required|max:20|sometimes',
            'logo' => 'nullable|sometimes|image|mimes:jpeg,png,jpg|max:2048|sometimes' ,
            'address' => 'required|sometimes',
            'sales_name' => 'required|max:100|sometimes'
        ];

        if($this->isMethod('patch')){
            $rules['clinic_id'] = ['required', 'max:50', 'sometimes',
                Rule::unique('partners')->ignore($clinicId, 'clinic_id')
            ];
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
            'clinic_id.required' => 'Kolom ID klinik harus diisi!',
            'clinic_id.max' => 'Kolom ID klinik berisikan maksimal 50 huruf!',
            'clinic_id.unique' => 'ID klinik sudah digunakan!',
            'name.required' => 'Kolom nama mitra harus diisi!',
            'email.required' => 'Kolom email mitra harus diisi!',
            'email.max' => 'Kolom email mitra berisikan maksimal 50 huruf!',
            'phone.required' => 'Kolom phone nomor telephone mitrass harus diisi!',
            'phone.max' => 'Kolom nomor telephone mitra berisikan maksimal 20 angka!',
            'logo.image' => 'Kolom logo mitra harus berisikan gambar!',
            'logo.mimes' => 'Kolom logo mitra harus memiliki tipe data .jpg, .jpeg, atau .png!',
            'logo.max' => 'Kolom logo mitra berukuran maksimal 2 MB!',
            'address.required' => 'Kolom alamat mitra harus diisi!',
            'sales_name.required' => 'Kolom nama sales harus diisi!',
        ];
    }
}
