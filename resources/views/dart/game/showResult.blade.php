@extends('layouts.dart')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dart.css') }}" />
@endpush

@section('content')
<div class="container px-2">
    <h2> Game Winner </h2>
    <div class="row px-4 align-items-end podium-container">
        <div class="col p-0">
            <div class="podium podium-bronze">
                <span class="podium-place"> #3 </span>
                <span class="podium-user"> Kristina Cole </span>
            </div>
        </div>
        <div class="col p-0">
            <div class="podium podium-gold">
                <i class="fa-solid fa-trophy mb-3" style="font-size: 1.1em"></i>
                {{-- <span class="podium-place"> #1 </span> --}}
                <span class="podium-user"> Blubber Lounge </span>
            </div>
        </div>
        <div class="col p-0">
            <div class="podium podium-silver">
                <span class="podium-place"> #2 </span>
                <span class="podium-user"> Erick Prohaska </span>
            </div>
        </div>
    </div>
    <div class="row">
        <table class="table table-striped table-hover">
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
                        <th scope="row">1</th>
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

    <hr class="py-4" />

    {{-- <h2> Highest Streak </h2>
    <div class="row px-4 align-items-end podium-container">
        <div class="col p-0">
            <div class="podium podium-bronze">
                <span class="podium-place"> #3 </span>
                <span class="podium-user"> Kristina Cole </span>
            </div>
        </div>
        <div class="col p-0">
            <div class="podium podium-gold">
                <i class="fa-solid fa-trophy mb-3" style="font-size: 1.1em"></i>
                {{-- <span class="podium-place"> #1 </span> --}}
                {{-- <span class="podium-user"> Blubber Lounge </span>
            </div>
        </div>
        <div class="col p-0">
            <div class="podium podium-silver">
                <span class="podium-place"> #2 </span>
                <span class="podium-user"> Erick Prohaska </span>
            </div>
        </div>
    </div> --}}
</div>
@endsection
