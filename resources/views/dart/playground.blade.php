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
        <div class="col">
            <input type="hidden" id="gameId" name="id" value="99ec098a-cd74-4223-ab1f-3bca516ce8fa">
            <div id="dartboardContainer" class="flex relative justify-center items-center p-2" style="width: 100%; height: 400px;">
                <div id="skeleton-dartboard" class="absolute top-50 start-50 translate-middle">
                    <div class="spinner-border">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap pb-5">
        <div class="col justify-center">
            <div id="myDiv"></div>
        </div>
    </div>
</div>
@endsection
