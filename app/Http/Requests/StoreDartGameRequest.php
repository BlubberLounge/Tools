<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

use App\Enums\DartGameType;
use App\Enums\DartGameStatus;

class StoreDartGameRequest extends FormRequest
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
            'users.*' => 'required|exists:users,id',
            'created_at' => 'exists:users,id',
            'type' => [new Enum(DartGameType::class)],
            'status' => [new Enum(DartGameStatus::class)],
            // 'private' => 'nullable|accepted',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:512',
            'points' => 'nullable|integer',
            'start' => 'nullable|integer',
            'end' => 'nullable|integer',
            'fields' => 'nullable|json',
            'singleOut' => 'required|accepted',
            'doubleOut' => 'required|accepted',
            'trippleOut' => 'required|accepted',
            // 'singleIn' => 'accepted',
            // 'doubleIn' => 'accepted',
            // 'trippleIn' => 'accepted',
        ];
    }
}
