@extends('layouts.dart')

@push('scripts')
    {{-- <script src="{{ asset('js/confetti.min.js') }}"></script> // cool, ultra lightweight, but only supports click event --}}
    @vite(['resources/js/dart/game/dartResult.js'])
    @vite(['resources/js/dart/game/dartResultHeatmap.js'])
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
@endpush

@push('styles')
    @vite(['resources/sass/dartboardResult.scss'])
@endpush

@section('content')
{{-- <div class="flex flex-wrap m-0">
    <div id="scroll-container">
        <div id="scroll-text"> {{ Str::repeat('  WINNER: '.$firstPlaceUser->full_name, 5 ) }} </div>
    </div>
</div> --}}
<div class="container mx-auto px-4 md:px-0">
    <div class="flex flex-wrap mr-0 justify-center">
        <div class="flex-1 md:w-10/12 lg:w-8/12 md:max-w-[83%] lg:max-w-[66%]">
            <input type="hidden" id="gameId" name="id" value="{{ $id }}">
            <div class="flex flex-wrap px-2 mt-5 items-end podium-container">
                <div class="w-4/12 p-0">
                    <div @class(["podium podium-bronze", "podium-bronze-outline" => count($users) <= 2])>
                        <span class="podium-place"> #3 </span>
                        <span class="podium-user"> {{ $thirdPlaceUser->full_name ?? $thirdPlaceUser }} </span>
                    </div>
                </div>
                {{-- <div @class(["w-4/12 p-0", "ml-[33%]" => count($users) <= 2])> --}}
                <div class="w-4/12 p-0">
                    <div class="podium podium-gold">
                        <i id="confetti" class="fa-solid fa-trophy mb-3" style="font-size: 1.1em"></i>
                        {{-- <span class="podium-place"> #1 </span> --}}
                        <span class="podium-user"> {{ $firstPlaceUser->full_name }} </span>
                    </div>
                </div>
                <div class="w-4/12 p-0">
                    <div @class(["podium podium-silver", "podium-silver-outline" => count($users) <= 1])>
                        <span class="podium-place"> #2 </span>
                        <span class="podium-user"> {{ $secondPlaceUser->full_name ?? $secondPlaceUser }} </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th scope="col"> # </th>
                            <th scope="col"> Name </th>
                            <th scope="col" class="text-center"> Average </th>
                            <th scope="col" class="text-center"> numThrows </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <th scope="row">{{ $user->place }}</th>
                                <td>{{ $user->full_name }}</td>
                                <td class="text-center">{{ $user->getThrowAverage($id) }}</td>
                                <td class="text-center">{{ $user->getThrowCount($id) }}</td>
                            </tr>
                        @empty
                            <tr>
                                No Data...
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <hr class="my-4 mb-5" />

            <h2> Quick Statistics </h2>
            <div class="flex flex-wrap mb-4">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th scope="col"> # </th>
                            <th scope="col"> Name </th>
                            <th scope="col" class="text-center"> Data </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row"> highest throw </th>
                            <td> {{ $stats['highestThrow']->user->full_name }} </td>
                            <td> {{ $stats['highestThrow']->value }} points ({{ $stats['highestThrow']->ring->value . $stats['highestThrow']->field }}) </td>
                        </tr>
                        <tr>
                            <th scope="row"> lowest throw </th>
                            <td> {{ $stats['lowestThrow']->user->full_name }} </td>
                            <td> {{ $stats['lowestThrow']->value }} points </td>
                        </tr>
                        <tr>
                            <th scope="row"> best turn </th>
                            <td> {{ $stats['bestTurn']->user->full_name }} </td>
                            <td> {{ $stats['bestTurn']->turn_total }} points </td> {{-- 180 (3x T20) --}}
                        </tr>
                        <tr>
                            <th scope="row"> worst turn </th>
                            <td> {{ $stats['worstTurn']->user->full_name }} </td>
                            <td> {{ $stats['worstTurn']->turn_total }} points </td> {{-- 0 (3x 0) --}}
                        </tr>
                        <tr>
                            <th scope="row"> highest accuracy </th>
                            <td> {{ $stats['streak'] }} </td>
                            <td> / </td>
                        </tr>
                        <tr>
                            <th scope="row"> longest streak </th>
                            <td> {{ $stats['longestStreak']['user']->full_name }} </td>
                            <td> {{ $stats['longestStreak']['streakCount'] }}x ({{ $stats['longestStreak']['field'] }}) </td> {{-- 33x (T20) --}}
                        </tr>
                        {{-- <tr>
                            <th scope="row"> highest streak </th>
                            <td> {{ $stats['streak'] }} </td>
                            <td> 69x (DB) </td>
                        </tr> --}}
                        @if($stats['mostMisthrows'])
                            <tr>
                                <th scope="row"> most misthrows </th>
                                <td> {{ $stats['mostMisthrows']['user']->full_name }} </td>
                                <td> {{ $stats['mostMisthrows']['misthrowCount'] }}x miss </td>
                            </tr>
                        @endif
                        <tr>
                            <th scope="row" colspan="3" class="text-center"> {{ Str::repeat('=', 15 )}} All Time {{ Str::repeat('=', 15 )}} </th>
                        </tr>
                        <tr>
                            <th scope="row"> win streak </th>
                            <td> {{ $stats['streak'] }} </td>
                            <td> 3 games </td>
                        </tr>
                        <tr>
                            <th scope="row"> lose streak </th>
                            <td> {{ $stats['streak'] }} </td>
                            <td> 8 games </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-4">
                @foreach ($users as $i => $user)
                    <div class="pb-5">
                        <div id="heatmap{{ $i+1 }}" class="flex relative justify-center items-center p-2 heatmap heatmap{{ $i+1 }}" style="width: 100%; height: 300px;">
                            <div id="skeleton-heatmap{{ $i+1 }}" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                                <div class="loading loading-spinner loading-md">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Floating Button -->
            <a class="btn btn-primary btn-floating" href="{{ route('dart.game.index') }}" role="button">
                <i class="fa-solid fa-house-chimney"></i>
            </a>
        </div>
    </div>

</div>
@endsection
