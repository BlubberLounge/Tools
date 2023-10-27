@extends('layouts.app')

@push('scripts')
    {{-- <script src="{{ mix('js/airsoft-calculator.js') }}" defer></script> --}}
@endpush

@section('content')
<div class="container">
    <div class="row">
        <div class="col">
            <h2>Airsoft FPS Chart</h2>
            <p>
                Wie lang muss der Lauf sein für eine Cylinder zu Lauf Relation von 1.5 : 1? <br>
                Volumen Cylinder * 1.5 = Volumen Lauf <br>
                a^2 * π * b = c^2 * π * d
                <span class="d-block ms-3"> => <span class="ms-3"> d = ( a^2*b ) / ( c^2*x ) </span> </span>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <h2>Airsoft FPS Chart</h2>
            <table class="table table-dark table-striped">
                <thead class="sticky-top">
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
                                    <td class="{{ $joule >= 4 ? 'table-primary' : ($joule >= 2.5 ? 'table-danger' : ($joule >= 1.2 ? 'table-warning' : 'table-success')) }}">{{ $joule  }}</td>
                                @endforeach
                            </tr>
                        @endfor
                  </tbody>
              </table>
        </div>
    </div>
</div>
@endsection
