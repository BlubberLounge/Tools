@extends('layouts.app')

@section('content')
<div class="container-fluid px-0" id="container-faq">
    <section id="faq-description" class="text-center" style="padding: 2rem 3rem 1rem 3rem;">
        <i class="fa-solid fa-comment-dots mb-3" style="font-size: 4rem"></i>
        <h1> FAQ </h1>
        <p> Frequently asked questions with their answers </p>
    </section>
    <section class="px-0">
        <div class="accordion accordion-flush" id="accordionfeedback">
            @forelse ($FAQList as $QA)
                @php($headerID = 'header-'.$QA->id)
                @php($bodyID = 'body-'.$QA->id)
                <div class="accordion-item position-relative mb-4">
                    <h2 class="accordion-header" id="{{ $headerID }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $bodyID }}" aria-expanded="false" aria-controls="{{ $bodyID }}">
                            <span style="margin-right: auto">
                                {{ Str::limit($QA->title, 45) }}
                            </span>
                        </button>
                    </h2>
                    <div id="{{ $bodyID }}" class="accordion-collapse collapse" aria-labelledby="{{ $headerID }}" data-bs-parent="#accordionfeedback">
                        <div class="accordion-body p-4" style="word-break: break-all;">
                            <div class="row">
                                {{ $QA->content }}
                            </div>
                    </div>
                </div>
            @empty
            <div class="row" style="color:var(--bl-clr-background-light);">
                <div class="col">
                    no FAQ
                </div>
            </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
