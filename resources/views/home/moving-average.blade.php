@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/moving-average.js'])
@endpush

@section('content')
<div class="container">

    <div class="flex flex-wrap justify-center items-center mb-5">
        <div id="movingAveragePlot"></div>
    </div>

</div>
@endsection
