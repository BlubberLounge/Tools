@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/home.js'])
@endpush

@push('styles')
    <style>
        .dashboard-card {
            background-color: #ffffff;
            border-radius: 0.75rem;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            transition: all 0.2s ease;
            break-inside: avoid;
            margin-bottom: 1.5rem;
        }
        .dark .dashboard-card {
            background-color: #1e293b;
            border-color: #334155;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.3);
        }
        .dashboard-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }
        .dark .dashboard-card:hover {
            border-color: #475569;
        }
        .dashboard-card-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .dark .dashboard-card-header {
            border-bottom-color: #334155;
        }
        .dashboard-card-header i {
            color: #f97316;
        }
        .dashboard-card-header h5 {
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        .dark .dashboard-card-header h5 {
            color: #e2e8f0;
        }
        .queue-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 0.5rem;
            background-color: #f1f5f9;
            margin-bottom: 0.5rem;
        }
        .dark .queue-item {
            background-color: #334155;
        }
        .queue-item-child {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            padding-left: 2rem;
            font-size: 0.875rem;
            color: #475569;
        }
        .dark .queue-item-child {
            color: #94a3b8;
        }
        .player-score-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
            border-radius: 0.5rem;
            background-color: #f1f5f9;
        }
        .dark .player-score-card {
            background-color: #334155;
        }
        .btn-primary-gradient {
            background: linear-gradient(to right, #f97316, #ea580c);
            color: white;
            font-weight: 600;
            padding: 0.625rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-primary-gradient:hover {
            background: linear-gradient(to right, #ea580c, #c2410c);
        }
        .btn-primary-gradient:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .btn-outline-custom {
            border: 2px solid #cbd5e1;
            color: #475569;
            font-weight: 600;
            padding: 0.625rem 1.5rem;
            border-radius: 0.5rem;
            background: transparent;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .dark .btn-outline-custom {
            border-color: #475569;
            color: #cbd5e1;
        }
        .btn-outline-custom:hover {
            border-color: #f97316;
            color: #f97316;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-badge.success {
            background-color: rgb(34 197 94 / 0.2);
            color: #16a34a;
        }
        .dark .status-badge.success {
            color: #4ade80;
        }
        .status-badge.danger {
            background-color: rgb(239 68 68 / 0.2);
            color: #dc2626;
        }
        .dark .status-badge.danger {
            color: #f87171;
        }
    </style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">

    <!-- Welcome Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-2">
            <div class="w-12 h-12 rounded-full bg-orange-500/10 flex items-center justify-center">
                <i class="fa-solid fa-hand-wave text-xl text-orange-500"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
                    {{ __('Welcome back') }}, {{ Auth::user()->name }}!
                </h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm">
                    {{ now()->format('l, j F Y') }}
                </p>
            </div>
        </div>
        @if (session('status'))
            <div class="mt-4 p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-green-600 dark:text-green-400">
                {{ session('status') }}
            </div>
        @endif
    </div>

    <!-- Dashboard Masonry Grid -->
    <div class="columns-1 lg:columns-2 gap-6 space-y-6">

        <!-- Active Dart Game -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="fa-solid fa-bullseye text-lg"></i>
                <h5>{{ __('active dart game') }}</h5>
            </div>
            @if($activeDartGame)
                <div class="text-center mb-4">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-500/10 text-orange-500 text-sm font-medium">
                        <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                        {{ $activeDartGame->title }}
                    </span>
                </div>
                <div class="grid grid-cols-{{ min(count($activeDartGame->users), 4) }} gap-3">
                    @foreach ($activeDartGame->users as $user)
                        <div class="player-score-card">
                            <span class="text-sm text-slate-600 dark:text-slate-400 mb-1">{{ $user->name }}</span>
                            <span class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $activeDartGame->remainingPointsByUser($user) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 text-center">
                    <a href="{{ route('dart.game.show', $activeDartGame) }}" class="btn-primary-gradient inline-flex items-center gap-2">
                        <i class="fa-solid fa-play"></i>
                        {{ __('continue game') }}
                    </a>
                </div>
            @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center">
                        <i class="fa-solid fa-gamepad text-2xl text-slate-400 dark:text-slate-500"></i>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 mb-4">{{ __('no active game') }}</p>
                    <a href="{{ route('dart.game.index') }}" class="btn-outline-custom inline-flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i>
                        {{ __('start new game') }}
                    </a>
                </div>
            @endif
        </div>

        <!-- Dart Queue -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="fa-solid fa-list-ol text-lg"></i>
                <h5>{{ __('dart queue') }}</h5>
                <div class="ml-auto">
                    @if(Auth::user()->isOnDartQueue())
                        <span class="status-badge success">
                            <i class="fa-solid fa-check text-xs"></i>
                            {{ __('in queue') }}
                        </span>
                    @else
                        <span class="status-badge danger">
                            <i class="fa-solid fa-xmark text-xs"></i>
                            {{ __('not in queue') }}
                        </span>
                    @endif
                </div>
            </div>

            <div id="dartQueueList" class="max-h-[300px] overflow-y-auto custom-scrollbar mb-4">
                @forelse ($dartQueue as $queueItem)
                    <div class="queue-item">
                        <div class="w-8 h-8 rounded-full bg-orange-500/20 flex items-center justify-center text-orange-500 font-semibold text-sm">
                            {{ $loop->iteration }}
                        </div>
                        <div class="flex-1">
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $queueItem->parentUser->name }}</span>
                            <span class="text-slate-400 dark:text-slate-500 text-sm ml-2">{{ $queueItem->created_at->diffForHumans() }}</span>
                        </div>
                        @if(Auth::user()->id === $queueItem->parentUser->id)
                            <button id="btnQueueRemove" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        @endif
                    </div>
                    @foreach ($queueItem->parentUser->dartQueueChilds()->get() as $childUser)
                        <div class="queue-item-child">
                            <i class="fa-solid fa-user text-xs text-slate-400"></i>
                            {{ $childUser->name }}
                        </div>
                    @endforeach
                @empty
                    <div class="text-center py-8">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center">
                            <i class="fa-solid fa-inbox text-xl text-slate-400 dark:text-slate-500"></i>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __('queue is empty') }}</p>
                    </div>
                @endforelse
            </div>

            <div class="text-center pt-3 border-t border-slate-200 dark:border-slate-700">
                <button type="button" id="btnQueueAdd" class="btn-primary-gradient {{ Auth::user()->isOnDartQueue() ? 'opacity-50 cursor-not-allowed' : '' }}" {{ Auth::user()->isOnDartQueue() ? 'disabled' : '' }}>
                    <i class="fa-solid fa-plus mr-2"></i>
                    {{ __('join the queue') }}
                </button>
            </div>
        </div>

        <!-- Spotify Playlist -->
        <div class="dashboard-card p-0 overflow-hidden">
            <div class="dashboard-card-header px-4 pt-4 border-b-0">
                <i class="fa-brands fa-spotify text-lg text-green-500"></i>
                <h5>{{ __('recommended playlist') }}</h5>
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

        <!-- Quick Actions -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="fa-solid fa-bolt text-lg"></i>
                <h5>{{ __('quick actions') }}</h5>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('dart.game.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-orange-500/10 hover:border-orange-500/20 border border-transparent transition-all group">
                    <div class="w-10 h-10 rounded-full bg-orange-500/10 flex items-center justify-center group-hover:bg-orange-500/20 transition-colors">
                        <i class="fa-solid fa-bullseye text-orange-500"></i>
                    </div>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('new game') }}</span>
                </a>
                <a href="{{ route('dart.show-checkout-calculator') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-orange-500/10 hover:border-orange-500/20 border border-transparent transition-all group">
                    <div class="w-10 h-10 rounded-full bg-orange-500/10 flex items-center justify-center group-hover:bg-orange-500/20 transition-colors">
                        <i class="fa-solid fa-calculator text-orange-500"></i>
                    </div>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('checkouts') }}</span>
                </a>
                <a href="{{ route('dart.show-info') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-orange-500/10 hover:border-orange-500/20 border border-transparent transition-all group">
                    <div class="w-10 h-10 rounded-full bg-orange-500/10 flex items-center justify-center group-hover:bg-orange-500/20 transition-colors">
                        <i class="fa-solid fa-circle-info text-orange-500"></i>
                    </div>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('info') }}</span>
                </a>
                <button id="BtnShare" class="flex flex-col items-center gap-2 p-4 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-orange-500/10 hover:border-orange-500/20 border border-transparent transition-all group">
                    <div class="w-10 h-10 rounded-full bg-orange-500/10 flex items-center justify-center group-hover:bg-orange-500/20 transition-colors">
                        <i class="fa-solid fa-share-nodes text-orange-500"></i>
                    </div>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('share') }}</span>
                </button>
            </div>
        </div>

    </div>

</div>
@endsection
