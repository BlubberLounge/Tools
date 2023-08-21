@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dartboard.css') }}" />
@endpush

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <ul class="list-group">
                @foreach ($games as $game)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        @mobile
                            {{ Str::limit($game->title, 40) }}
                        @endmobile
                        @desktop
                            {{ $game->title }}
                        @enddesktop
                        <span class="badge rounded-pill" style="background-color: {{ $game->status->color() }}"> {{ $game->status }} </span>
                    </li>
                @endforeach
              </ul>
        </div>
    </div>
</div>
@endsection
