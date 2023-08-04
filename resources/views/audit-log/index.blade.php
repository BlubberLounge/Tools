@extends('layouts.app')

{{-- @push('scripts')
    <script src="{{ mix('js/settings.js') }}" defer></script>
@endpush --}}

@section('content')
    <div class="container">
        <table id="table-auditLog" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ID</th>
                    <th>Event</th>
                    <th>Auditable Type:ID</th>
                    {{-- <th scope="col">Old Values</th>
                    <th scope="col">New Values</th> --}}
                    <th>Changes</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($audits as $audit)
                    <tr>
                        <th>{{ $audit->id }}</th>
                        <td>{{ $audit->user_id }}</td>
                        <td>{{ $audit->event }}</td>
                        <td>{{ str_replace('App\Models\\', '', $audit->auditable_type) }}: {{ $audit->auditable_id }}</td>
                        {{-- <td style="word-wrap: break-word;max-width: 160px;">
                            @foreach($audit->old_values as $key => $val)
                                {!! '<b>'.$key.':</b> '. $val . '<br>' !!}
                            @endforeach
                        </td>
                        <td style="word-wrap: break-word;max-width: 160px;">
                            @foreach($audit->new_values as $key => $val)
                                {!! '<b>'.$key.':</b> '. $val . '<br>' !!}
                            @endforeach</td> --}}
                        <td style="word-wrap: break-word;max-width: 300px;">
                            @forelse($audit->old_values as $key => $val)
                                <div style="font-weight: bold">
                                    {{ $key }}
                                </div>
                                <div class="ps-3">
                                    {{ $val }}
                                    <span style="color:var(--bl-clr-red)"> => </span>
                                    {{ $audit->new_values[$key] }}
                                </div>
                            @empty
                                @forelse($audit->new_values as $key => $val)
                                    <div>
                                        <span style="font-weight: bold">{{ $key }}: </span> {{ $val }}
                                    </div>
                                @empty
                                    <span style="color:var(--bl-clr-red)"> No data </span>
                                @endforelse
                            @endforelse
                        </td>
                        <td style="word-wrap: break-word;max-width: 100px;">{{ $audit->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            {!! $audits->links() !!}
        </div>
    </div>
@endsection
