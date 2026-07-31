<?php

namespace App\Http\Requests;

use App\Enums\NotificationChannel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'min:1',
                Rule::exists('users', 'id'),
            ],
            'channel' => [
                'required',
                Rule::enum(NotificationChannel::class),
            ],
            'message' => [
                'required',
                'string',
                'max:500',
            ],
        ];
    }
}
