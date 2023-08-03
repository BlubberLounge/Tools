@extends('layouts.dart')

@push('scripts')
    <script src="{{ mix('js/dart.js') }}"></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dart.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/dartboard.css') }}" />
@endpush

@section('content')
<div class="container px-2">
    <input type="hidden" id="gameId" name="id" value="{{ $id }}">
    <input type="hidden" id="gameType" name="type" value="{{ $type }}">

    @if($type === App\Enums\DartGameType::X01)
        @include('dart.game.showX01')
    @elseif ($type === App\Enums\DartGameType::aroundTheClock)
        @include('dart.game.showAroundTheClock')
    @elseif ($type === App\Enums\DartGameType::cricket)
        @include('dart.game.showCricket')
    @endif

    <div class="row justify-center mt-4 align-items-center">
        <div id="dartboard" style="position:relative; display:flex; justify-content:center;">
    </div>
</div>
@endsection
