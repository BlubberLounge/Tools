@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/feedback-index.js'])
@endpush

@section('content')
<div class="container mx-auto px-4">
    <section id="feedback-description" class="text-center pt-8 pb-4 px-6">
        <i class="fa-regular fa-comments" style="font-size: 4rem"></i>
        <h1> Feedback </h1>
        <p> Liste aller User Feedbacks </p>
    </section>
    <section>
        <div class="space-y-2" x-data="{ openId: null }">
            @forelse ($feedbackList as $feedback)
                <div class="card card-bordered bg-base-100 relative mb-4" data-bl-feedback-id="{{ $feedback->id }}">
                    <div class="absolute -top-2.5 left-3 z-10">
                        <span class="badge feedback-type-{{ $feedback->type }}" style="background-color:{{ $feedback->type->color() }};"> {{ $feedback->type }} </span>
                    </div>
                    <button @click="openId = openId === '{{ $feedback->id }}' ? null : '{{ $feedback->id }}'"
                        class="flex items-center justify-between w-full text-left font-medium pt-4 pb-2 px-2"
                        data-bl-feedback-status="{{ $feedback->status }}">
                        <span class="mr-auto">
                            {{ Str::limit($feedback->subject, 40) }}
                        </span>
                        <i class="fa-solid fa-eye fa-lg feedback-seen-icon mr-2" style="color: #000; {{ $feedback->status === \App\Enums\FeedbackStatus::SEEN ?: 'display: none;'}}"></i>
                        @if ($feedback->status === \App\Enums\FeedbackStatus::GOOD)
                            <i class="fa-solid fa-thumbs-up fa-lg mr-2" style="color: var(--bl-clr-green);"></i>
                        @elseif ($feedback->status === \App\Enums\FeedbackStatus::BAD)
                            <i class="fa-solid fa-thumbs-down fa-lg mr-2" style="color: var(--bl-clr-red);"></i>
                        @endif
                        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="openId === '{{ $feedback->id }}' ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openId === '{{ $feedback->id }}'" x-collapse class="mt-3 p-4 break-all">
                        <div>
                            @if($feedback->status !== \App\Enums\FeedbackStatus::GOOD && $feedback->status !== \App\Enums\FeedbackStatus::BAD)
                                <div class="flex gap-0 mb-4" role="group">
                                    <button type="button" class="btn btn-success btn-feedback-rating" data-bl-feedback-status="good">
                                        <i class="fa-regular fa-thumbs-up"></i>
                                        Good
                                    </button>
                                    <button type="button" class="btn btn-error btn-feedback-rating" data-bl-feedback-status="bad">
                                        <i class="fa-regular fa-thumbs-down"></i>
                                        Bad
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="mt-3">
                            <h3>Subject</h3>
                        </div>
                        <div class="mb-3">
                            {{ $feedback->subject }}
                        </div>
                        <div class="mt-3">
                            <h3>Message</h3>
                        </div>
                        <div class="mb-5">
                            {{ $feedback->message }}
                        </div>
                        <div class="mt-3">
                            <h3>Additional Information</h3>
                        </div>
                        <div>
                            <b>Bereich:</b> {{ $feedback->area }}
                        </div>
                        <div>
                            <b>Eingereicht am:</b> {{ $feedback->created_at->format('d.m.Y - h:i:s') }}
                        </div>
                        <div>
                            <b>Letztes update:</b> {{ $feedback->updated_at->format('d.m.Y - h:i:s') }}
                        </div>
                        <div class="mt-3">
                            <h3>Device Information</h3>
                        </div>
                        <div>
                            <b>Family:</b> {{ $feedback->device->device_family }}
                        </div>
                        <div>
                            <b>Model:</b> {{ $feedback->device->device_model }}
                        </div>
                        <div>
                            <b>platform:</b> {{ $feedback->device->platform }}
                        </div>
                        <div>
                            <b>Browser:</b> {{ $feedback->device->browser }}
                        </div>
                    </div>
                </div>
            @empty
            <div class="text-base-content/50">
                no feedback has been send yet
            </div>
            @endforelse
        </div>
        @if ($feedbackList->hasPages())
            <div class="flex justify-center">
                {!! $feedbackList->links() !!}
            </div>
        @endif
    </section>
</div>
@endsection
