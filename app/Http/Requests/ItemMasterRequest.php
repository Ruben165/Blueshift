<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemMasterRequest extends FormRequest
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
            'name'      => 'required|max:50',
            'sku'       => 'required|max:50|unique:items,sku',
            'content'   => 'required|max:255',
            'packaging' => 'required|max:50',
            'unit'      => 'required|max:50',
            'manufacturer'      => 'required|max:50',
            'price'     => 'required|numeric|min:0'
        ];
        if($this->isMethod('PATCH')){
            $rules['sku'] .= ','.$this->item->id;
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
            'name.max' => 'Kolom nama berisikan maksimal 50 huruf!',
            'sku.required' => 'Kolom SKU harus diisi!',
            'sku.max' => 'Kolom SKU berisikan maksimal 50 huruf!',
            'sku.unique' => 'SKU sudah digunakan!',
            'content.required' => 'Kolom kandungan harus diisi!',
            'content.max' => 'Kolom kandungan berisikan maksimal 255 huruf!',
            'packaging.required' => 'Kolom kemasan harus diisi!',
            'packaging.max' => 'Kolom kemasan berisikan maksimal 50 huruf!',
            'unit.required' => 'Kolom satuan harus diisi!',
            'unit.max' => 'Kolom satuan berisikan maksimal 255 huruf!',
            'manufacturer.required' => 'Kolom pabrik harus diisi!',
            'manufacturer.max' => 'Kolom pabrik berisikan maksimal 50 huruf!',
            'price.required' => 'Kolom harga harus diisi!',
            'price.numeric' => 'Kolom harga harus berupa angka!',
            'price.min' => 'Kolom harga tidak boleh lebih kecil dari 0!',
        ];
    }
}
