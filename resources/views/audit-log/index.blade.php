@extends('layouts.app')

@section('content')
<div class="container">

    @include('audit-log.includes.title')

    <div class="row justify-content-between g-5 pb-1">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">User ID</th>
                    <th scope="col">Event</th>
                    <th scope="col">Auditable Type</th>
                    <th scope="col">Auditable ID</th>
                    <th scope="col">Old Values</th>
                    <th scope="col">New Values</th>
                    <th scope="col">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($audits as $audit)
                    <tr>
                        <th scope="row">{{ $audit->id }}</th>
                        <td scope="row">{{ $audit->user_id }}</td>
                        <td scope="row">{{ $audit->event }}</td>
                        <td scope="row">{{ $audit->auditable_type }}</td>
                        <td scope="row">{{ $audit->auditable_id }}</td>
                        <td scope="row">{{ json_encode($audit->old_values) }}</td>
                        <td scope="row">{{ json_encode($audit->new_values) }}</td>
                        <td scope="row">{{ $audit->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
