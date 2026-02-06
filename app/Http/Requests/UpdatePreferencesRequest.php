<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_subscription' => ['sometimes', 'boolean'],
            'tip_notification' => ['sometimes', 'boolean'],
            'new_message' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_subscription.boolean' => 'Geçersiz bildirim değeri.',
            'tip_notification.boolean' => 'Geçersiz bildirim değeri.',
            'new_message.boolean' => 'Geçersiz bildirim değeri.',
        ];
    }
}
