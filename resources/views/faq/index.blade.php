@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4" id="container-faq">
    <section id="faq-description" class="text-center pt-8 pb-4 px-6">
        <i class="fa-solid fa-comment-dots mb-3" style="font-size: 4rem"></i>
        <h1> FAQ </h1>
        <p> Frequently asked questions with their answers </p>
    </section>
    <section>
        <div class="space-y-2" x-data="{ openId: null }">
            @forelse ($FAQList as $QA)
                <div class="card card-bordered bg-base-100 p-4">
                    <button @click="openId = openId === {{ $QA->id }} ? null : {{ $QA->id }}"
                        class="flex items-center justify-between w-full text-left font-medium py-2 px-2">
                        <span class="mr-auto">
                            {{ Str::limit($QA->title, 45) }}
                        </span>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="openId === {{ $QA->id }} ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openId === {{ $QA->id }}" x-collapse class="mt-3 p-4 break-all">
                        {{ $QA->content }}
                    </div>
                </div>
            @empty
            <div class="text-base-content/50">
                no FAQ
            </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
