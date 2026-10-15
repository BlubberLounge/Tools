@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/feedback-create.js'])
@endpush

@section('content')
<div class="container mx-auto px-4">
    <div class="flex flex-wrap text-center my-5">
        <h1> Feedback </h1>
    </div>
    <div id="container-feedback">
        <div class="flex flex-wrap justify-center">
            <div class="w-auto">
                <button class="btn btn-dark btn-feedback" value="{{ App\Enums\FeedbackType::ENHANCEMENT }}">
                    <div class="card card-bordered bg-base-100 p-4">
                        <div class="p-4 flex flex-col" style="min-width: 125px;">
                            <i class="fa-solid fa-wand-magic-sparkles mb-2" style="font-size: 1.2em"></i>
                            Enhancement
                        </div>
                    </div>
                </button>
            </div>
            <div class="w-auto">
                <button class="btn btn-dark btn-feedback" value="{{ App\Enums\FeedbackType::BUG }}">
                    <div class="card card-bordered bg-base-100 p-4">
                        <div class="p-4 flex flex-col" style="min-width: 125px;">
                            <i class="fa-solid fa-bug mb-2" style="font-size: 1.2em"></i>
                            Bug
                        </div>
                    </div>
                </button>
            </div>
            <div class="w-auto">
                <button class="btn btn-dark btn-feedback" value="{{ App\Enums\FeedbackType::INFORMATION }}">
                    <div class="card card-bordered bg-base-100 p-4">
                        <div class="p-4 flex flex-col" style="min-width: 125px;">
                            <i class="fa-solid fa-circle-info mb-2" style="font-size: 1.2em"></i>
                            Information
                        </div>
                    </div>
                </button>
            </div>
            <div class="w-auto">
                <button class="btn btn-dark btn-feedback" value="{{ App\Enums\FeedbackType::GENERAL }}">
                    <div class="card card-bordered bg-base-100 p-4">
                        <div class="p-4 flex flex-col" style="min-width: 125px;">
                            <i class="fa-regular fa-comments mb-2" style="font-size: 1.2em"></i>
                            Other
                        </div>
                    </div>
                </button>
            </div>
        </div>

        <hr class="my-4" />

        <table class="table table-zebra">
            <thead>
                <tr>
                    <th scope="col"> # </th>
                    <th scope="col"> Status </th>
                    <th scope="col"> Type </th>
                    <th scope="col"> Subject </th>
                </tr>
            </thead>
            <tbody>
                @forelse (Auth::user()->feedback as $f)
                    <tr>
                        {{-- <th scope="row">{{ $loop->index + 1 }}</th> --}}
                        <th scope="row"> {{ $f->created_at->format('d.m.Y') }} </th>
                        <td>
                            <span class="badge" style="background-color:{{ $f->status->color() }};"> {{ $f->status }} </span>
                        </td>
                        <td>
                            <span class="badge" style="background-color:{{ $f->type->color() }};"> {{ $f->type }} </span>
                        </td>
                        <td>
                            {{ Str::limit($f->subject, 50) }}
                        </td>
                    </tr>
                    @empty
                    <tr>

                    </tr>
                    @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex flex-wrap justify-center mt-3 px-5" id="container-form-feedback-create" style="display: none">
        @include('feedback.feedback-create', ['options' => $options])
    </div>
</div>
@endsection
