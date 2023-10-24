@extends('layouts.app')

@push('scripts')
    {{-- <script src="{{ mix('js/airsoft-calculator.js') }}" defer></script> --}}
@endpush

@section('content')
<div class="container">
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
                                        $joule = number_format((float)(round($i * $i * $gramm * .5) / 1000), 2, '.', '')
                                    @endphp
                                    <td class="{{ $joule >= 3 ? 'table-primary' : ($joule >= 2 ? 'table-danger' : ($joule >= 1.2 ? 'table-warning' : 'table-success')) }}">{{ $joule  }}</td>
                                @endforeach
                            </tr>
                        @endfor
                  </tbody>
              </table>
        </div>
    </div>
</div>
@endsection
