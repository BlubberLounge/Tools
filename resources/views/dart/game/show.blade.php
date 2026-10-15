@extends('layouts.dart')

@push('scripts')
    @vite(['resources/js/dart/game/show.js'])
@endpush

@push('styles')
    @vite(['resources/sass/dartboard.scss'])
@endpush

@section('content')
<div class="container mx-auto px-2 md:px-0 mt-3">
    <div class="flex flex-wrap mr-0 justify-center">
        <div class="flex-1 md:w-8/12 lg:w-6/12 md:max-w-[66%] lg:max-w-[50%]">
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
                <div class="flex flex-wrap justify-center items-center">
                    {{-- <div id="skeleton-dartboard" class="placeholder-wave" style="position:relative; display:flex; justify-content:center; width: 360px; height: 360px;">
                        <span class="placeholder col"></span>
                    </div> --}}
                    <div id="skeleton-dartboard" class="flex justify-center items-center" style="width: 360px; height: 360px;">
                        <div class="loading loading-spinner loading-md">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>

                    <div id="dartboard" style="position:relative; display:flex; justify-content:center;"></div>
                </div>
            </section>

            <section class="mt-3 mb-4">
                <div class="flex flex-wrap justify-center items-center">
                    <div class="join w-8/12 md:w-7/12">
                        <button type="button" class="btn btn-error join-item"> Abort </button>
                        <button type="button" class="btn btn-warning join-item disabled"> Pause </button>
                        <button type="button" id="keepOn" class="btn btn-error join-item"> noSleep </button>
                    </div>
                </div>
                <div class="flex flex-wrap justify-center items-center mt-4">
                    <div class="w-8/12 md:w-7/12">
                        <label for="volume-control" class="label">
                            Volume
                            <span id="volume-percentage"> 65 </span> %
                        </label>
                        <input type="range" class="range range-primary" id="volume-control" min="0" max="1" step=".05" value=".65">
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
