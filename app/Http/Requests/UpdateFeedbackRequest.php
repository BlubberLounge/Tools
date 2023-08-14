<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

use App\Enums\FedbackType;
use App\Enums\FedbackStatus;


class UpdateFeedbackRequest extends FormRequest
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
            'type'      => 'in:general,bug,information,enhancement',
            'status'    => 'in:new,seen,good,bad',
            'subject'   => 'string',
            'message'   => 'string',
            'area'      => 'string',
            'device_id' => 'exists:devices,id',
        ];
    }
}
