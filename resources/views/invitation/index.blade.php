@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/invitation.js'])
@endpush

@section('content')
<div class="container">
    <div class="flex flex-wrap justify-center">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col" class="hidden d-xl-table-cell">{{ __('firstname') }}</th>
                    <th scope="col" class="hidden d-xl-table-cell">{{ __('lastname') }}</th>
                    <th scope="col">{{ __('email') }}</th>
                    <th scope="col" class="hidden d-xl-table-cell">{{ __('send') }}</th>
                    <th scope="col" class="hidden d-xl-table-cell">{{ __('expires at') }}</th>
                    <th scope="col" class="text-center">{{ __('actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invitations as $invitation)
                    <tr>
                        <th scope="row">{{ $invitation->id }}</th>
                        <td class="hidden d-xl-table-cell">{{ $invitation->firstname }}</td>
                        <td class="hidden d-xl-table-cell">{{ $invitation->lastname }}</td>
                        <td>{{ $invitation->email }}</td>
                        <td class="hidden d-xl-table-cell">{{ $invitation->created_at->format('d.m.Y') }}</td>
                        <td class="hidden d-xl-table-cell">{{ $invitation->expires_at ? $invitation->expires_at->format('d.m.Y') : '/' }}</td>
                        <td class="text-center">
                            @if ($invitation->status === App\Enums\InvitationStatus::NEW || $invitation->status === App\Enums\InvitationStatus::UNKOWN)
                                <button type="button" class="btn btn-primary btn-approve" data-invitation-id="{{ $invitation->id }}">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-primary btn-denie" data-invitation-id="{{ $invitation->id }}">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            @elseif ($invitation->isExpired() || $invitation->status === App\Enums\InvitationStatus::APPROVED || $invitation->status === App\Enums\InvitationStatus::DENIED )
                                <span class="badge" style="background-color: {{ $invitation->status->color() }}">{{ $invitation->status }}</span>
                            @endif
                            {{-- <form action="{{ route('invitation.approve', $invitation->id) }}" method="POST">
                                @csrf

                                <button type="submit" class="btn text-black">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </form> --}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="flex justify-center">
            {!! $invitations->links() !!}
        </div>
    </div>
</div>
@endsection
