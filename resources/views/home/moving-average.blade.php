@extends('layouts.app')

@push('scripts')
    <script src="{{ mix('js/moving-average.js') }}" defer></script>
@endpush

@section('content')
<div class="container">

    <div class="row justify-center align-items-center mb-5">
        <div id="movingAveragePlot"></div>
    </div>

</div>
@endsection
