@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/moving-average.js'])
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">

    <div class="card card-bordered bg-base-100 p-4">
        <div class="flex justify-center items-center">
            <div id="movingAveragePlot"></div>
        </div>
    </div>

</div>
@endsection
