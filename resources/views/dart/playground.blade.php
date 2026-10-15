@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/dart/dartPlayground.js'])
@endpush

@push('styles')
    @vite(['resources/sass/dartboardResult.scss'])
@endpush

@section('content')
<div class="container">

    <div class="flex flex-wrap pb-5">
        <div class="flex-1">
            <input type="hidden" id="gameId" name="id" value="99ec098a-cd74-4223-ab1f-3bca516ce8fa">
            <div id="dartboardContainer" class="flex relative justify-center items-center p-2" style="width: 100%; height: 400px;">
                <div id="skeleton-dartboard" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                    <div class="spinner">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap pb-5">
        <div class="flex-1 justify-center">
            <div id="myDiv"></div>
        </div>
    </div>
</div>
@endsection
