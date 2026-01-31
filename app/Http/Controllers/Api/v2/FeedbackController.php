<?php

namespace App\Http\Controllers\Api\v2;

use App\Enums\FeedbackStatus;
use App\Enums\FeedbackType;
use App\Http\Requests\Api\StoreFeedbackApiRequest;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;

class FeedbackController extends Controller
{
    /**
     * Store feedback from the DartApp.
     *
     * Accepts bug reports and general feedback submissions.
     */
    public function store(StoreFeedbackApiRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Map the incoming type/category to FeedbackType
        $feedbackType = $this->mapToFeedbackType(
            $validated['type'],
            $validated['category'] ?? null
        );

        // Build subject line based on type
        $subject = $this->buildSubject($validated);

        // Create the feedback record
        $feedback = new Feedback([
            'type' => $feedbackType,
            'severity' => $validated['severity'] ?? null,
            'status' => FeedbackStatus::NEW,
            'subject' => $subject,
            'message' => $validated['description'],
            'steps_to_reproduce' => $validated['stepsToReproduce'] ?? null,
            'area' => 'DartApp',
            'device_info' => $validated['deviceInfo'] ?? null,
        ]);

        $feedback->user()->associate($user);
        $feedback->save();

        return $this->sendResponse(
            [
                'id' => $feedback->id,
            ],
            $validated['type'] === 'bug'
                ? 'Bug report received. Thank you for helping improve the app!'
                : 'Feedback received. We appreciate your input!'
        );
    }

    /**
     * Map DartApp type/category to FeedbackType enum.
     */
    private function mapToFeedbackType(string $type, ?string $category): FeedbackType
    {
        if ($type === 'bug') {
            return FeedbackType::BUG;
        }

        // Map general feedback categories
        return match ($category) {
            'feature' => FeedbackType::ENHANCEMENT,
            'improvement' => FeedbackType::ENHANCEMENT,
            default => FeedbackType::GENERAL,
        };
    }

    /**
     * Build a descriptive subject line.
     */
    private function buildSubject(array $data): string
    {
        if ($data['type'] === 'bug') {
            $severity = ucfirst($data['severity'] ?? 'unknown');
            return "[DartApp Bug - {$severity}] " . $this->truncate($data['description'], 50);
        }

        $category = ucfirst($data['category'] ?? 'general');
        return "[DartApp {$category}] " . $this->truncate($data['description'], 50);
    }

    /**
     * Truncate a string to a maximum length.
     */
    private function truncate(string $text, int $maxLength): string
    {
        if (strlen($text) <= $maxLength) {
            return $text;
        }

        return substr($text, 0, $maxLength - 3) . '...';
    }
}
