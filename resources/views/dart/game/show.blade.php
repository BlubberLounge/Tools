@extends('layouts.dart')

@push('scripts')
    <script src="{{ mix('js/dart.js') }}"></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dart.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/dartboard.css') }}" />
@endpush

@section('content')
<div class="container px-4">
    <div class="p-3" style="overflow-x: auto;white-space: nowrap;">
        @foreach ($users as $user)
            <div class="card d-inline-block me-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto">
                            301
                        </div>
                        <div class="col">
                            {{ $user->full_name }}
                        </div>
                        <div class="col">
                            Wins: 0
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            1
                        </div>
                        <div class="col">
                            2
                        </div>
                        <div class="col">
                            3
                        </div>
                        <div class="col">
                            Total
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="row justify-center mt-4">
        <div class="col-md-6">
            <div id="dartboard" style="width: 350px; height: 350px; position: relative;">
        </div>
    </div>
</div>
@endsection
