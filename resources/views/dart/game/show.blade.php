@extends('layouts.dart')

@push('scripts')
    <script src="{{ mix('js/dart.js') }}"></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dartboard.css') }}" />
@endpush

@section('content')
<div class="container px-2 mt-3">
    <input type="hidden" id="gameId" name="id" value="{{ $id }}">
    <input type="hidden" id="gameType" name="type" value="{{ $type }}">

    @if($type === App\Enums\DartGameType::X01)
        @include('dart.game.showX01')
    @elseif ($type === App\Enums\DartGameType::aroundTheClock)
        @include('dart.game.showAroundTheClock')
    @elseif ($type === App\Enums\DartGameType::cricket)
        @include('dart.game.showCricket')
    @endif

    <section class="mt-4 mb-5">
        <div class="row justify-center align-items-center">
            {{-- <div id="skeleton-dartboard" class="placeholder-wave" style="position:relative; display:flex; justify-content:center; width: 360px; height: 360px;">
                <span class="placeholder col"></span>
            </div> --}}
            <div id="skeleton-dartboard" class=" d-flex justify-center align-items-center" style="width: 360px; height: 360px;">
                <div class="spinner-border">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div id="dartboard" style="position:relative; display:flex; justify-content:center;"></div>
        </div>
    </section>

    <section class="mt-3 mb-4">
        <div class="row justify-center align-items-center">
            <div class="btn-group col-6 col-md-5">
                <button type="button" class="btn btn-danger"> Abort </button>
                <button type="button" class="btn btn-warning disabled"> Pause </button>
                <button type="button" id="keepOn" class="btn btn-danger"> noSleep </button>
            </div>
    </section>
</div>
@endsection
