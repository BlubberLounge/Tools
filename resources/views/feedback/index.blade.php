@extends('layouts.app')

@push('scripts')
    <script src="{{ mix('js/feedback-index.js') }}" defer></script>
@endpush

@section('content')
<div class="container-fluid px-0">
    <section id="feedback-description" class="text-center" style="padding: 2rem 3rem 1rem 3rem;">
        <i class="fa-regular fa-comments" style="font-size: 4rem"></i>
        <h1> Feedback </h1>
        <p> Liste aller User Feedbacks </p>
    </section>
    <section class="px-0">
        <div class="accordion accordion-flush" id="accordionfeedback">
            @forelse ($feedbackList as $feedback)
                @php($headerID = 'header-'.$feedback->id)
                @php($bodyID = 'body-'.$feedback->id)
                <div class="accordion-item position-relative mb-4" data-bl-feedback-id="{{ $feedback->id }}">
                    <div class="position-absolute" style="z-index: 100;top:-10px;">
                        <span class="badge rounded-pill feedback-type-{{ $feedback->type }}" style="background-color:{{ $feedback->type->color() }};"> {{ $feedback->type }} </span>
                    </div>
                    <h2 class="accordion-header feedback-type-{{ $feedback->type }}" id="{{ $headerID }}">
                        <button class="accordion-button collapsed" type="button" data-bl-feedback-status="{{ $feedback->status }}" data-bs-toggle="collapse" data-bs-target="#{{ $bodyID }}" aria-expanded="false" aria-controls="{{ $bodyID }}">
                            <span style="margin-right: auto">
                                {{ Str::limit($feedback->subject, 40) }}
                            </span>
                            <i class="fa-solid fa-eye fa-lg feedback-seen-icon" style="color: #000; {{ $feedback->status === \App\Enums\FeedbackStatus::SEEN ?: 'display: none;'}}"></i>
                            @if ($feedback->status === \App\Enums\FeedbackStatus::GOOD)
                                <i class="fa-solid fa-thumbs-up fa-lg" style="color: var(--bl-clr-green);"></i>
                            @elseif ($feedback->status === \App\Enums\FeedbackStatus::BAD)
                                <i class="fa-solid fa-thumbs-down fa-lg" style="color: var(--bl-clr-red);"></i>
                            @else

                            @endif
                        </button>
                    </h2>
                    <div id="{{ $bodyID }}" class="accordion-collapse collapse" aria-labelledby="{{ $headerID }}" data-bs-parent="#accordionfeedback">
                        <div class="accordion-body p-4" style="word-break: break-all;">
                            <div class="row">
                                @if($feedback->status !== \App\Enums\FeedbackStatus::GOOD && $feedback->status !== \App\Enums\FeedbackStatus::BAD)
                                    <div class="btn-group mb-4" role="group">
                                        <button type="button" class="btn btn-success btn-feedback-rating" data-bl-feedback-status="good">
                                            <i class="fa-regular fa-thumbs-up"></i>
                                            Good
                                        </button>
                                        <button type="button" class="btn btn-danger btn-feedback-rating" data-bl-feedback-status="bad">
                                            <i class="fa-regular fa-thumbs-down"></i>
                                            Bad
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="row mt-3">
                                <h3>
                                    Subject
                                </h3>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    {{ $feedback->subject }}
                                </div>
                            </div>
                            <div class="row mt-3">
                                <h3>
                                    Message
                                </h3>
                            </div>
                            <div class="row mb-5">
                                <div class="col">
                                    {{ $feedback->message }}
                                </div>
                            </div>
                            <div class="row mt-3">
                                <h3>
                                    Additional Information
                                </h3>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <b>Bereich:</b> {{ $feedback->area }}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <b>Eingereicht am:</b> {{ $feedback->created_at->format('d.m.Y - h:i:s') }}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <b>Letztes update:</b> {{ $feedback->updated_at->format('d.m.Y - h:i:s') }}
                                </div>
                            </div>
                            <div class="row mt-3">
                                <h3>
                                    Device Information
                                </h3>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <b>Family:</b> {{ $feedback->device->device_family }}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <b>Model:</b> {{ $feedback->device->device_model }}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <b>platform:</b> {{ $feedback->device->platform }}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <b>Browser:</b> {{ $feedback->device->browser }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
            <div class="row" style="color:var(--bl-clr-background-light);">
                <div class="col">
                    no feedback has been send yet
                </div>
            </div>
            @endforelse
        </div>
        @if ($feedbackList->hasPages())
            <div class="d-flex justify-content-center">
                {!! $feedbackList->links() !!}
            </div>
        @endif
    </section>
</div>
@endsection
