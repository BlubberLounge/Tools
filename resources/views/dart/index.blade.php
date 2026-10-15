@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/dart/dartIndex.js'])
@endpush

@push('styles')
    @vite(['resources/sass/dartboardResult.scss'])
@endpush

@section('content')
<div class="container">
    <div class="flex flex-wrap justify-around items-center mb-4">
        <div class="w-full md:flex-1 rounded mb-4 md:mb-0 md:mr-4 bg-base-200" style="height: 600px;">
            <div id="skillsGraph" style="height: 100%"></div>
        </div>
        <div class="w-full md:flex-1">
            <div id="winRateGraph" class="flex flex-wrap rounded mb-4 bg-base-200" style="height:280px;"></div>
            <div id="positionGraph" class="flex flex-wrap rounded bg-base-200" style="height:280px;"></div>
        </div>
    </div>

    <div class="flex flex-wrap justify-center items-center mb-4">
        <div id="activityGraph" class="flex-1 rounded bg-base-200" style="height:300px;"></div>
    </div>

    <div class="flex flex-wrap justify-around items-center mb-4">
        <div id="gameTypesGraph" class="w-full md:flex-1 rounded mr-4 bg-base-200" style="height:280px;"></div>
        <div id="" class="w-full md:flex-1 hidden rounded mr-4 bg-base-200" style="height:280px;"></div>
        <div id="" class="w-full md:flex-1 hidden rounded bg-base-200" style="height:280px;"></div>
    </div>

    <div class="flex flex-wrap mb-5 justify-center">
        <div class="w-10/12 md:w-7/12">
            <select id="gameSelection" class="select select-bordered w-full">
                {{-- <option selected>Open this select menu</option> --}}
                @foreach ($games as $game)
                    <option value="{{ $game->id }}" {{ $loop->first ? 'selected' : null }}>
                        @mobile
                            {{ Str::limit($game->title, 25) }}
                        @endmobile
                        @desktop
                            {{ $game->title }}
                        @enddesktop
                        {{-- <span class="badge" style="background-color: {{ $game->status->color() }}"> {{ $game->status }} </span> --}}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="flex flex-wrap mb-5">
        <div class="flex-1 md:w-6/12" style="height: 400px">
            <div id="dartboardContainer" class="flex relative justify-center items-center p-2 h-full w-full">
                <div id="skeleton-dartboard" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                    <div class="loading loading-spinner loading-md">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex-1 md:w-6/12 flex justify-center items-center h-full">
            <div id="graph01"></div>
        </div>
    </div>

    <div class="flex flex-wrap justify-center items-center mb-5">
        <div id="expectationDataGraph" class="flex-1" style="height: 500px;"></div>
    </div>
</div>
@endsection
