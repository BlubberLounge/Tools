@extends('layouts.app')

{{-- @push('scripts')
    @vite(['resources/js/settings.js'])
@endpush --}}

@section('content')
    <div class="container mx-auto px-4">
        <table id="table-auditLog" class="table table-zebra">
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
                        <td class="wrap-break-word max-w-75">
                            @forelse($audit->old_values as $key => $val)
                                <div class="font-bold">
                                    {{ $key }}
                                </div>
                                <div class="pl-3">
                                    {{ $val }}
                                    <span class="text-error"> => </span>
                                    @if (array_key_exists($key, $audit->new_values))
                                        {{ $audit->new_values[$key] }}
                                    @endif
                                </div>
                            @empty
                                @forelse($audit->new_values as $key => $val)
                                    <div>
                                        <span class="font-bold">{{ $key }}: </span> {{ $val }}
                                    </div>
                                @empty
                                    <span class="text-error"> No data </span>
                                @endforelse
                            @endforelse
                        </td>
                        <td class="wrap-break-word max-w-25">{{ $audit->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="flex justify-center">
            {!! $audits->links() !!}
        </div>
    </div>
@endsection
