<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class)->ignore($this->user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user->id)],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
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
            'name.required' => 'Kolom nama harus diisi!',
            'name.max' => 'Kolom nama berisikan maksimal 255 huruf!',
            'name.unique' => 'Nama sudah digunakan!',
            'username.required' => 'Kolom username harus diisi!',
            'username.max' => 'Kolom username berisikan maksimal 255 huruf!',
            'username.unique' => 'Username sudah digunakan!',
            'email.required' => 'Kolom email harus diisi!',
            'email.max' => 'Kolom email berisikan maksimal 255 huruf!',
            'email.unique' => 'Email sudah digunakan!',
            'password.required' => 'Kolom password harus diisi!',
            'password.confirmed' => 'Kolom konfirmasi password tidak sama!',
        ];
    }
}
