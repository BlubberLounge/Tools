<?php

namespace App\Http\Requests\Api;

use App\Enums\FeedbackType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreFeedbackApiRequest extends FormRequest
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
            'type' => ['required', 'in:bug,feedback'],
            'description' => ['required', 'string', 'min:10'],

            // Bug report specific fields
            'severity' => ['required_if:type,bug', 'in:low,medium,high'],
            'stepsToReproduce' => ['nullable', 'string'],

            // General feedback specific fields
            'category' => ['required_if:type,feedback', 'in:feature,improvement,other'],

            // Device info (optional)
            'deviceInfo' => ['nullable', 'array'],
            'deviceInfo.userAgent' => ['nullable', 'string'],
            'deviceInfo.language' => ['nullable', 'string'],
            'deviceInfo.platform' => ['nullable', 'string'],
            'deviceInfo.screenWidth' => ['nullable', 'string'],
            'deviceInfo.screenHeight' => ['nullable', 'string'],
            'deviceInfo.colorDepth' => ['nullable', 'string'],
            'deviceInfo.timezone' => ['nullable', 'string'],
            'deviceInfo.appVersion' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Feedback type is required.',
            'type.in' => 'Feedback type must be either "bug" or "feedback".',
            'description.required' => 'Description is required.',
            'description.min' => 'Description must be at least 10 characters.',
            'severity.required_if' => 'Severity is required for bug reports.',
            'severity.in' => 'Severity must be low, medium, or high.',
            'category.required_if' => 'Category is required for general feedback.',
            'category.in' => 'Category must be feature, improvement, or other.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422));
    }
}
