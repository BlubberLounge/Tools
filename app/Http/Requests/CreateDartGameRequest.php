<?php

namespace App\Http\Requests;

use App\Enums\DartGameType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateDartGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', function ($attribute, $value, $fail) {
                try {
                    DartGameType::fromString($value);
                } catch (\ValueError $e) {
                    $fail("Invalid game type: {$value}");
                }
            }],
            'private' => ['boolean'],
            'title' => ['required', 'string'],
            'comment' => ['nullable', 'string'],

            'points' => ['integer', 'required_if:type,' . DartGameType::X01->value],
            'start' => ['nullable', 'integer'],
            'end' => ['nullable', 'integer'],
            'fields' => ['nullable', 'string'],

            'singleOut' => ['boolean'],
            'doubleOut' => ['boolean'],
            'trippleOut' => ['boolean'],

            'singleIn' => ['boolean'],
            'doubleIn' => ['boolean'],
            'trippleIn' => ['boolean'],

            'users' => ['nullable', 'array'],
            'users.*' => ['integer', 'exists:users,id'],

            // Play type (standard, team, tournament)
            'game_type' => ['nullable', 'string', 'in:standard,team,tournament'],

            // Mode-specific options
            'options' => ['nullable', 'array'],
            'options.checkout' => ['nullable', 'string', 'in:any,single,double,master'],
            'options.hitsPerField' => ['nullable', 'integer', 'min:1', 'max:4'],
            'options.includeBull' => ['nullable', 'boolean'],
            'options.rounds' => ['nullable', 'integer', 'min:1', 'max:20'],

            // Team data (for team game creation)
            'teams' => ['nullable', 'array', 'required_if:game_type,team'],
            'teams.*.name' => ['required_with:teams', 'string', 'max:50'],
            'teams.*.color' => ['required_with:teams', 'string', 'max:7'],
            'teams.*.players' => ['required_with:teams', 'array'],
            'teams.*.players.*' => ['integer', 'exists:users,id'],
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
