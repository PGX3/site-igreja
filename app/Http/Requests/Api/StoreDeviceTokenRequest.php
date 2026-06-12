<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'token'       => ['required', 'string', 'max:255'],
            'platform'    => ['required', Rule::in(['ios', 'android', 'web'])],
            'app_version' => ['nullable', 'string', 'max:30'],
        ];
    }
}
