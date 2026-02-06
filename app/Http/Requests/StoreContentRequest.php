<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCreator() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'visibility' => ['required', 'in:public,registered_only,subscriber_only,ppv,tier_only'],
            'ppv_price_atomic' => ['nullable', 'integer', 'min:1', 'required_if:visibility,ppv'],
            'ppv_currency' => ['nullable', 'string', 'max:10'],
            'required_tier_id' => ['nullable', 'string', 'exists:tiers,id', 'required_if:visibility,tier_only'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Başlık gerekli.',
            'visibility.required' => 'Görünürlük seçimi gerekli.',
            'ppv_price_atomic.required_if' => 'PPV fiyatı gerekli.',
            'ppv_price_atomic.min' => 'PPV fiyatı en az 1 olmalı.',
        ];
    }
}
