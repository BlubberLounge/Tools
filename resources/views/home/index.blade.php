@extends('layouts.app')

@push('scripts')
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
                <div class="rounded" style="max-height: 300px;overflow-y:scroll;">
                    <ul class="list-group list-group-flush">
                        @forelse  ($dartGames as $dartGame)
                            <li class="list-group-item">
                                <div class="me-auto">
                                    <span class="text-body-secondary">{{ $dartGame->title }}</span> vom {{ $dartGame->created_at->format('d.m.Y') }}
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
