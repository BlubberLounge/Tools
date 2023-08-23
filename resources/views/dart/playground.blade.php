@extends('layouts.app')

@push('scripts')
    <script defer src="{{ mix('js/dartPlayground.js') }}"></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dartboardResult.css') }}" />
@endpush

@section('content')
<div class="container">

    <div class="row pb-5">
        <div class="col">
            <input type="hidden" id="gameId" name="id" value="99ec098a-cd74-4223-ab1f-3bca516ce8fa">
            <div id="dartboardContainer" class="d-flex position-relative justify-center align-items-center p-2" style="width: 100%; height: 400px;">
                <div id="skeleton-dartboard" class="position-absolute top-50 start-50 translate-middle">
                    <div class="spinner-border">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row pb-5">
        <div class="col justify-center">
            <div id="myDiv"></div>
        </div>
    </div>
</div>
@endsection
