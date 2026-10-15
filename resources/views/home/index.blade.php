@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/home.js'])
@endpush

@section('content')
<div class="container mx-auto">

    {{-- Welcome --}}
    <div class="mb-6">
        <h1 class="text-xl font-bold">{{ __('Welcome back') }}, {{ Auth::user()->name }}</h1>
        <p class="text-base-content/50 text-sm">{{ now()->format('l, j F Y') }}</p>
        @if (session('status'))
            <div class="alert alert-success mt-3">{{ session('status') }}</div>
        @endif
    </div>

    {{-- Dashboard Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Left Column --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Active Dart Game --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-sm">
                        <i class="fa-solid fa-bullseye text-primary"></i>
                        {{ __('active dart game') }}
                    </h2>
                    @if($activeDartGame)
                        <div class="text-center my-2">
                            <span class="badge badge-primary badge-outline gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                                {{ $activeDartGame->title }}
                            </span>
                        </div>
                        <div class="stats stats-horizontal shadow w-full">
                            @foreach ($activeDartGame->users as $user)
                                <div class="stat place-items-center py-3">
                                    <div class="stat-title text-xs">{{ $user->name }}</div>
                                    <div class="stat-value text-2xl">{{ $activeDartGame->remainingPointsByUser($user) }}</div>
                                </div>
                            @endforeach
                        </div>
                        <div class="card-actions justify-center mt-3">
                            <a href="{{ route('dart.game.show', $activeDartGame) }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-play"></i> {{ __('continue game') }}
                            </a>
                        </div>
                    @else
                        <div class="flex flex-col items-center py-8 text-base-content/40">
                            <i class="fa-solid fa-gamepad text-4xl mb-3"></i>
                            <p class="mb-3">{{ __('no active game') }}</p>
                            <a href="{{ route('dart.game.index') }}" class="btn btn-ghost btn-sm">
                                <i class="fa-solid fa-plus"></i> {{ __('start new game') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Dart Queue --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h2 class="card-title text-sm">
                            <i class="fa-solid fa-list-ol text-primary"></i>
                            {{ __('dart queue') }}
                        </h2>
                        @if(Auth::user()->isOnDartQueue())
                            <span class="badge badge-success badge-sm gap-1"><i class="fa-solid fa-check text-xs"></i>{{ __('in queue') }}</span>
                        @else
                            <span class="badge badge-error badge-sm gap-1"><i class="fa-solid fa-xmark text-xs"></i>{{ __('not in queue') }}</span>
                        @endif
                    </div>

                    <div id="dartQueueList" class="max-h-72 overflow-y-auto custom-scrollbar mt-2">
                        @forelse ($dartQueue as $queueItem)
                            <div class="flex items-center gap-3 py-2 px-1">
                                <div class="badge badge-primary badge-outline font-bold">{{ $loop->iteration }}</div>
                                <div class="flex-1">
                                    <span class="font-medium">{{ $queueItem->parentUser->name }}</span>
                                    <span class="text-base-content/40 text-xs ml-1">{{ $queueItem->created_at->diffForHumans() }}</span>
                                </div>
                                @if(Auth::user()->id === $queueItem->parentUser->id)
                                    <button id="btnQueueRemove" class="btn btn-ghost btn-xs btn-circle text-error">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                @endif
                            </div>
                            @foreach ($queueItem->parentUser->dartQueueChilds()->get() as $childUser)
                                <div class="flex items-center gap-2 pl-10 py-1 text-sm text-base-content/50">
                                    <i class="fa-solid fa-user text-xs"></i>
                                    {{ $childUser->name }}
                                </div>
                            @endforeach
                        @empty
                            <div class="flex flex-col items-center py-8 text-base-content/40">
                                <i class="fa-solid fa-inbox text-3xl mb-2"></i>
                                <p class="text-sm">{{ __('queue is empty') }}</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="card-actions justify-center mt-2 pt-3 border-t border-base-300">
                        <button type="button" id="btnQueueAdd" class="btn btn-primary btn-sm" {{ Auth::user()->isOnDartQueue() ? 'disabled' : '' }}>
                            <i class="fa-solid fa-plus"></i> {{ __('join the queue') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-4">

            {{-- Spotify --}}
            <div class="card bg-base-100 shadow-sm overflow-hidden">
                <div class="card-body p-3 pb-0">
                    <h2 class="card-title text-sm">
                        <i class="fa-brands fa-spotify text-success"></i>
                        {{ __('recommended playlist') }}
                    </h2>
                </div>
                <iframe
                    class="w-full"
                    src="https://open.spotify.com/embed/playlist/3GvdXXnCuBES5QH8W8NWVx?utm_source=generator&theme=0"
                    height="380"
                    frameBorder="0"
                    allowfullscreen=""
                    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                    loading="lazy">
                </iframe>
            </div>

            {{-- Quick Actions --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-sm">
                        <i class="fa-solid fa-bolt text-warning"></i>
                        {{ __('quick actions') }}
                    </h2>
                    <div class="grid grid-cols-2 gap-2 mt-1">
                        <a href="{{ route('dart.game.index') }}" class="btn btn-ghost btn-sm justify-start gap-2">
                            <i class="fa-solid fa-bullseye text-primary w-4"></i> {{ __('new game') }}
                        </a>
                        <a href="{{ route('dart.show-checkout-calculator') }}" class="btn btn-ghost btn-sm justify-start gap-2">
                            <i class="fa-solid fa-calculator text-primary w-4"></i> {{ __('checkouts') }}
                        </a>
                        <a href="{{ route('dart.show-info') }}" class="btn btn-ghost btn-sm justify-start gap-2">
                            <i class="fa-solid fa-circle-info text-primary w-4"></i> {{ __('info') }}
                        </a>
                        <button id="BtnShare" class="btn btn-ghost btn-sm justify-start gap-2">
                            <i class="fa-solid fa-share-nodes text-primary w-4"></i> {{ __('share') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
