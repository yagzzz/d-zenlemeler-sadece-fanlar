<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username,' . $this->user()->id],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'İsim gerekli.',
            'username.required' => 'Kullanıcı adı gerekli.',
            'username.alpha_dash' => 'Kullanıcı adı yalnızca harf, rakam, tire ve alt çizgi içerebilir.',
            'username.unique' => 'Bu kullanıcı adı zaten alınmış.',
        ];
    }
}
