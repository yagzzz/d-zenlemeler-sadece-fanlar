<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'İsim gerekli.',
            'username.required' => 'Kullanıcı adı gerekli.',
            'username.alpha_dash' => 'Kullanıcı adı yalnızca harf, rakam, tire ve alt çizgi içerebilir.',
            'username.unique' => 'Bu kullanıcı adı zaten alınmış.',
            'email.required' => 'E-posta adresi gerekli.',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
            'password.required' => 'Şifre gerekli.',
            'password.min' => 'Şifre en az 8 karakter olmalı.',
            'password.confirmed' => 'Şifre onayı eşleşmiyor.',
        ];
    }
}
