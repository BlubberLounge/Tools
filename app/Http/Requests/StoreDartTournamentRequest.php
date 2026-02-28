<?php

namespace App\Http\Requests;

use App\Enums\DartGameType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDartTournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'format' => ['required', 'string', 'in:single_elimination,round_robin'],
            'game_mode' => ['required', 'string', function ($attribute, $value, $fail) {
                try {
                    DartGameType::fromString($value);
                } catch (\ValueError $e) {
                    $fail("Invalid game mode: {$value}");
                }
            }],
            'game_options' => ['nullable', 'array'],
            'seed_type' => ['nullable', 'string', 'in:manual,random'],
            'best_of' => ['nullable', 'integer', 'min:1', 'max:7', 'odd'],
            'participants' => ['nullable', 'array'],
            'participants.*' => ['integer', 'exists:users,id'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422));
    }
}
