<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

use App\Enums\FedbackType;
use App\Enums\FedbackStatus;

class StoreFeedbackRequest extends FormRequest
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
        return [
            'user_id'   => 'exists:users,id',
            // 'type'      => 'required|in:GENERAL,BUG,INFORMATION,ENHANCEMENT',
            'status'    => [new Enum(FedbackStatus::class)],
            'subject'   => 'required|string',
            'message'   => 'required|string',
            'area'      => 'required|string',
            'device_id' => 'exists:devices,id',
        ];
    }
}
