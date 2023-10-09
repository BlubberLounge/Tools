@extends('layouts.app')

@push('scripts')
    <script src="{{ mix('js/home.js') }}" defer></script>
    {{-- <script src="https://accounts.google.com/gsi/client" onload="console.log('TODO: add onload function')"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center mb-4">
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

    <div class="row g-4" data-masonry='{"percentPosition": true }'>
        <div class="col-12 col-lg-6 rounded">
            <div class="rounded p-3" style="background-color: rgba(var(--bs-tertiary-bg-rgb), 1)">
                <h5 class="mb-3">{{ __('active dart game') }}</h5>
                @if($activeDartGame)
                    <div>
                        <h6 class="text-center">{{ $activeDartGame->title }}</h6>
                        <div class="row p-0 m-0">
                            @foreach ($activeDartGame->users as $user)
                                <div class="col">
                                    <div class="row justify-center">
                                        {{ $user->name }}
                                    </div>
                                    <div class="row justify-center">
                                        {{ $activeDartGame->remainingPointsByUser($user) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-12 col-lg-6 rounded">
            <div class="rounded p-3" style="background-color: rgba(var(--bs-tertiary-bg-rgb), 1)">
                <h5 class="mb-3">{{ __('open dart games') }}</h5>
                <div class="rounded" style="max-height: 300px;overflow-y:auto;">
                    <ul class="list-group list-group-flush">
                        @forelse  ($dartGames as $dartGame)
                            <li class="list-group-item">
                                <div class="me-auto">
                                    {{ $dartGame->title }}
                                    <span class="text-body-secondary small">vom {{ $dartGame->created_at->format('d.m.Y') }}</span>
                                </div>
                                <span class="badge rounded-pill text-bg-{{ $dartGame->type->color() }}">{{ $dartGame->type }}</span>
                                <span class="badge rounded-pill text-bg-{{ $dartGame->getNumberOfPlayers() <= 2 ? 'secondary' : ($dartGame->getNumberOfPlayers() <= 3 ? 'primary' : 'warning') }}">{{ $dartGame->getNumberOfPlayers() }} Spieler</span>
                            </li>
                        @empty
                            @foreach(["no data", "....", "...", "..", "."] as $c)
                                <li class="list-group-item" style="opacity:{{.75-$loop->index*.2}};">
                                    <th scope="row">{{ $c }}</th>
                                </li>
                            @endforeach
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="rounded p-0 pt-3" style="background-color: rgba(var(--bs-tertiary-bg-rgb), 1)">
                <h5 class="px-3 mb-3">{{ __('recommended playlist') }}</h5>
                <iframe style="border-radius:12px" src="https://open.spotify.com/embed/playlist/2BlSEnWR554eq3VqnHmkuM?utm_source=generator" width="100%" height="400" frameBorder="0" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture" loading="lazy"></iframe>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="rounded p-3" style="background-color: rgba(var(--bs-tertiary-bg-rgb), 1)">
                <h5 class="mb-3">{{ __('dart queue') }}</h5>
                <p id="dartQueueText" class="text-{{ Auth::user()->isOnDartQueue() ? 'success' : 'danger' }} text-center">
                    @if(Auth::user()->isOnDartQueue())
                        {{ __('you are in the queue') }}
                    @else
                        {{ __('you are not in the queue') }}
                    @endif
                </p>
                <div class="rounded" style="max-height:400px;overflow-y:auto;">
                    <ol id="dartQueueList" class="list-group list-group-numbered list-group-flush list">
                        @forelse ($dartQueue as $queueItem)
                            <li class="list-group-item">
                                {{ $queueItem->parentUser->name }}
                                <span class="text-body-secondary small">{{ $queueItem->created_at->diffForHumans() }}</span>
                                @if(Auth::user()->id === $queueItem->parentUser->id)
                                    <button id="btnQueueRemove" class="btn p-0 text-danger h-100 float-end">
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
                <div class="row justify-center mt-5 mb-4">
                    <div class="col col-md-8">
                        <div class="d-grid">
                            {{-- <button type="button" class="btn btn-outline-warning"> {{ __('put me on the waiting list') }}</button> --}}
                            <button type="button" id="btnQueueAdd" class="btn btn-outline-warning {{ !Auth::user()->isOnDartQueue() ?: 'disabled' }}"> {{ __('join the queue') }}</button>
                        </div>
                    </div>
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
