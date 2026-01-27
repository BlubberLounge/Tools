@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/home.js'])
@endpush

@section('content')
<div class="container">
    <div class="flex flex-wrap justify-center mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="text-center">
                        {{ __('You are logged in!') }}
                    </div>
                    <div class="mt-5 text-center">
                        {!! Str::replace('-', '<div>-', Illuminate\Foundation\Inspiring::quotes()->random()) !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="columns-1 lg:columns-2 gap-4 space-y-4">
        <div class="break-inside-avoid">
            <div class="rounded p-3 bg-[var(--tw-body-tertiary-bg)]">
                <h5 class="mb-3 font-semibold">{{ __('active dart game') }}</h5>
                @if($activeDartGame)
                    <div>
                        <h6 class="text-center">{{ $activeDartGame->title }}</h6>
                        <div class="flex justify-around p-0 m-0">
                            @foreach ($activeDartGame->users as $user)
                                <div class="text-center">
                                    <div>{{ $user->name }}</div>
                                    <div>{{ $activeDartGame->remainingPointsByUser($user) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="break-inside-avoid">
            <div class="rounded p-3 bg-[var(--tw-body-tertiary-bg)]">
                <h5 class="mb-3 font-semibold">{{ __('personal qr-code') }}</h5>
                <div class="flex justify-center">
                    <img src="{{ $qrcode }}" alt="" width="300px">
                </div>
            </div>
        </div>

        <div class="break-inside-avoid">
            <div class="rounded p-0 pt-3 bg-[var(--tw-body-tertiary-bg)] overflow-hidden">
                <h5 class="px-3 mb-3 font-semibold">{{ __('recommended playlist') }}</h5>
                <iframe class="rounded-b-lg" src="https://open.spotify.com/embed/playlist/3GvdXXnCuBES5QH8W8NWVx?utm_source=generator" width="100%" height="400" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>
            </div>
        </div>

        <div class="break-inside-avoid">
            <div class="rounded p-3 bg-[var(--tw-body-tertiary-bg)]">
                <h5 class="mb-3 font-semibold">{{ __('dart queue') }}</h5>
                <p id="dartQueueText" class="text-{{ Auth::user()->isOnDartQueue() ? 'success' : 'danger' }} text-center">
                    @if(Auth::user()->isOnDartQueue())
                        {{ __('you are in the queue') }}
                    @else
                        {{ __('you are not in the queue') }}
                    @endif
                </p>
                <div class="rounded max-h-[400px] overflow-y-auto">
                    <ol id="dartQueueList" class="list-group list-group-numbered list-group-flush list">
                        @forelse ($dartQueue as $queueItem)
                            <li class="list-group-item">
                                {{ $queueItem->parentUser->name }}
                                <span class="text-[var(--tw-muted-color)] text-sm">{{ $queueItem->created_at->diffForHumans() }}</span>
                                @if(Auth::user()->id === $queueItem->parentUser->id)
                                    <button id="btnQueueRemove" class="btn p-0 text-danger h-full float-right">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                @endif
                                <ul class="list-group">
                                    @foreach ($queueItem->parentUser->dartQueueChilds()->get() as $childUser)
                                        <li class="list-group-item px-3 py-1 border-0">
                                            {{ $childUser->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @empty
                            @foreach(["no data", "....", "...", "..", "."] as $c)
                                <li class="list-group-item" style="opacity:{{.75-$loop->index*.2}};">
                                    <th scope="row">{{ $c }}</th>
                                </li>
                            @endforeach
                        @endforelse
                    </ol>
                </div>
                <div class="flex justify-center mt-5 mb-4">
                    <button type="button" id="btnQueueAdd" class="btn btn-outline-warning {{ !Auth::user()->isOnDartQueue() ?: 'disabled' }}"> {{ __('join the queue') }}</button>
                </div>
            </div>
        </div>

        <div class="break-inside-avoid">
            <div class="rounded p-3 bg-[var(--tw-body-tertiary-bg)]">
                <h5 class="mb-3 font-semibold">{{ __('share this application') }}</h5>
                <div class="flex justify-center">
                    <img src="{{ $qrcode }}" alt="" width="300px">
                </div>
                <div class="flex justify-center mt-3">
                    <button id="BtnShare" class="btn btn-secondary">{{ __('share') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- {!! $test !!} --}}
    {{-- <div class="row">
        <div id="g_id_onload"
            data-client_id="{{ env('GOOGLE_CLIENT_ID') }}"
            data-context="signin"
            data-ux_mode="popup"
            data-login_uri="/auth/callback"
            data-auto_prompt="false">
        </div>

        <div class="g_id_signin"
            data-type="standard"
            data-shape="rectangular"
            data-theme="outline"
            data-text="signup_with"
            data-size="large"
            data-logo_alignment="left">
        </div>
    </div> --}}
</div>
@endsection
