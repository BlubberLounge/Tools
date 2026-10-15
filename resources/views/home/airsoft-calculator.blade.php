@extends('layouts.app')

@push('scripts')
    {{-- <script src="{{ mix('js/airsoft-calculator.js') }}" defer></script> --}}
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">

    <div class="card card-bordered bg-base-100 mb-6">
        <div class="flex items-center gap-2 pb-3 mb-3 border-b border-base-300 text-primary">
            <i class="fa-solid fa-crosshairs text-lg"></i>
            <h5>Cylinder-to-Barrel Ratio</h5>
        </div>
        <p class="text-base-content/70 text-sm leading-relaxed">
            Wie lang muss der Lauf sein f&uuml;r eine Cylinder zu Lauf Relation von 1.5 : 1? <br>
            Volumen Cylinder * 1.5 = Volumen Lauf <br>
            a^2 * &pi; * b = c^2 * &pi; * d
            <span class="block ml-3"> => <span class="ml-3"> d = ( a^2*b ) / ( c^2*x ) </span> </span>
        </p>
    </div>

    <div class="card card-bordered bg-base-100 p-4">
        <div class="flex items-center gap-2 pb-3 mb-3 border-b border-base-300 text-primary">
            <i class="fa-solid fa-gauge-high text-lg"></i>
            <h5>Airsoft FPS Chart</h5>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="table table-zebra">
                <thead class="sticky top-0">
                    <tr>
                        <th scope="col"> M/s </th>
                        <th scope="col"> FPS </th>
                        @foreach ($ammoWeights as $gramm)
                            <th scope="col"> {{ $gramm }} g</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 55; $i <= 220; $i += 5)
                        <tr>
                            <th scope="row"> {{ $i }} </th>
                            <td> {{ round($i / 0.3048) }} </td>

                            @foreach ($ammoWeights as $gramm)
                                @php
                                    $joule = number_format((float)(($i * $i * $gramm * .5) / 1000), 2, '.', '')
                                @endphp
                                <td class="{{ $joule >= 4 ? 'bg-primary/20 text-primary' : ($joule >= 2.5 ? 'bg-error/20 text-error' : ($joule >= 1.2 ? 'bg-warning/20 text-warning' : 'bg-success/20 text-success')) }}">{{ $joule }}</td>
                            @endforeach
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
