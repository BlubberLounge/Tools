@extends('layouts.dart')

@push('scripts')
    @vite(['resources/js/dart/game/dartSetup.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const html = document.documentElement;
            const lightBtn = document.getElementById('theme-light');
            const darkBtn = document.getElementById('theme-dark');

            function setTheme(theme) {
                if (theme === 'dark') {
                    html.classList.add('dark');
                    darkBtn.classList.add('active');
                    lightBtn.classList.remove('active');
                } else {
                    html.classList.remove('dark');
                    lightBtn.classList.add('active');
                    darkBtn.classList.remove('active');
                }
                localStorage.setItem('dart-theme', theme);
            }

            // Initialize theme
            const savedTheme = localStorage.getItem('dart-theme') || 'dark';
            setTheme(savedTheme);

            lightBtn.addEventListener('click', () => setTheme('light'));
            darkBtn.addEventListener('click', () => setTheme('dark'));
        });
    </script>
@endpush

@push('styles')
    <style>
        body {
            @apply bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200;
        }

        .theme-toggle {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 100;
            display: flex;
            gap: 0.25rem;
            @apply bg-slate-200 dark:bg-slate-700;
            padding: 0.25rem;
            border-radius: 0.5rem;
        }
        .theme-toggle button {
            background: transparent;
            border: none;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            @apply text-slate-500 dark:text-slate-400;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .theme-toggle button:hover {
            @apply text-slate-800 dark:text-slate-200;
        }
        .theme-toggle button.active {
            @apply bg-orange-500 text-white;
        }

        .lobby-card {
            @apply bg-gradient-to-br from-white to-slate-100 dark:from-slate-800 dark:to-slate-900;
            @apply border border-slate-300 dark:border-slate-700;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .lobby-card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            @apply border-b border-slate-300 dark:border-slate-700;
        }
        .lobby-card-header i {
            font-size: 1.25rem;
            @apply text-orange-500;
        }
        .lobby-card-header h5 {
            margin: 0;
            font-weight: 600;
            @apply text-slate-800 dark:text-slate-200;
        }
        .game-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            @apply bg-gradient-to-br from-orange-500 to-orange-600;
            border-radius: 0.5rem;
            color: white;
            font-weight: 600;
            font-size: 1.25rem;
        }
        .btn-start {
            @apply bg-gradient-to-br from-green-500 to-green-600;
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(34, 197, 94, 0.3);
            color: white;
        }
        .btn-cancel {
            display: inline-flex;
            align-items: center;
            background: transparent;
            @apply border-2 border-red-500 text-red-500;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .btn-cancel:hover {
            @apply bg-red-500 text-white;
        }
        .player-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            @apply bg-slate-100 dark:bg-slate-700;
            border-radius: 0.75rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
        }
        .player-item:hover {
            @apply bg-slate-200 dark:bg-slate-600;
        }
        .player-item img, .player-item svg {
            border-radius: 50%;
            width: 40px;
            height: 40px;
        }
        .player-item .player-name {
            flex: 1;
            font-weight: 500;
            @apply text-slate-800 dark:text-slate-200;
        }
        .player-item .actions button {
            background: transparent;
            border: none;
            padding: 0.25rem 0.5rem;
            @apply text-slate-500 dark:text-slate-400;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .player-item .actions .btn-remove-player:hover {
            @apply text-red-500;
        }
        .player-item .actions .btn-down-player:hover {
            @apply text-orange-500;
        }
        .queue-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            @apply bg-slate-100 dark:bg-slate-700;
            border-radius: 0.75rem;
            margin-bottom: 0.5rem;
        }
        .queue-item img, .queue-item svg {
            border-radius: 50%;
            width: 36px;
            height: 36px;
        }
        .queue-item .btn-add-player,
        .queue-item .btn-add-all-players {
            background: transparent;
            border: none;
            @apply text-green-500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .queue-item .btn-add-player:hover,
        .queue-item .btn-add-all-players:hover {
            transform: scale(1.2);
        }
        .input-type-btn {
            padding: 0.75rem 1.25rem;
            @apply border-2 border-slate-300 dark:border-slate-700;
            background: transparent;
            @apply text-slate-800 dark:text-slate-200;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .input-type-btn:hover {
            @apply border-orange-500 text-orange-500;
        }
        .btn-check:checked + .input-type-btn {
            @apply bg-gradient-to-br from-orange-500 to-orange-600 border-orange-500 text-white;
        }
        .btn-check:disabled + .input-type-btn {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .checkout-option {
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
        }
        .search-input-group {
            display: flex;
            align-items: center;
            @apply bg-slate-100 dark:bg-slate-700;
            border-radius: 0.75rem;
            padding: 0.5rem 1rem;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        .search-input-group:focus-within {
            @apply border-orange-500;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
        }
        .search-input-group i {
            @apply text-slate-500 dark:text-slate-400;
            margin-right: 0.75rem;
        }
        .search-input-group input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            @apply text-slate-800 dark:text-slate-200;
        }
        .search-input-group input::placeholder {
            @apply text-slate-500 dark:text-slate-400;
        }
        .empty-queue-placeholder {
            text-align: center;
            padding: 2rem;
            @apply text-slate-500 dark:text-slate-400;
        }

        /* Form inputs override */
        .form-control, .form-check-input {
            @apply bg-slate-100 dark:bg-slate-700;
            @apply border-slate-300 dark:border-slate-600;
            @apply text-slate-800 dark:text-slate-200;
        }
        .form-control:focus {
            @apply bg-slate-100 dark:bg-slate-700 border-orange-500 text-slate-800 dark:text-slate-200;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
        }
        .form-label {
            @apply text-slate-800 dark:text-slate-200;
        }
        .form-text {
            @apply text-slate-500 dark:text-slate-400;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Theme Toggle -->
    <div class="theme-toggle">
        <button type="button" id="theme-light" title="Light mode">
            <i class="fa-solid fa-sun"></i>
        </button>
        <button type="button" id="theme-dark" title="Dark mode">
            <i class="fa-solid fa-moon"></i>
        </button>
    </div>

    <div class="flex flex-wrap justify-center">
        <form method="POST" id="dartGameCreateForm" action="{{ route('dart.game.store') }}" class="col col-md-10 col-lg-8 col-xl-6">
            @csrf

            <!-- Header -->
            <section id="dart-game-header" class="text-center mb-5">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-3" style="background: rgba(249, 115, 22, 0.1);">
                    <i class="fa-solid fa-crosshairs text-3xl" style="color: #f97316;"></i>
                </div>
                <h1 class="text-2xl font-bold mb-3">{{ __('create dart lobby') }}</h1>
                <div class="game-type-badge mx-auto mb-4">
                    <i class="fa-solid fa-bullseye"></i>
                    {{ __($dartGameType) }}
                </div>
                <input type="hidden" name="points" value="{{ $dartGameType }}">
                <div class="flex justify-center gap-3">
                    <a href="{{ route('dart.game.index') }}" class="btn-cancel">
                        <i class="fa-solid fa-xmark mr-2"></i>{{ __('cancel') }}
                    </a>
                    <button type="submit" class="btn-start">
                        <i class="fa-solid fa-play mr-2"></i>{{ __('start') }}
                    </button>
                </div>
            </section>

            <!-- Game Settings -->
            <section id="dart-game-settings" class="lobby-card">
                <div class="lobby-card-header">
                    <i class="fa-solid fa-gear"></i>
                    <h5>{{ __('general') }}</h5>
                </div>

                <x-form.input-text attribute="title" label="game title" bottomSpacing="3" />
                <x-form.input-checkbox attribute="private" label="private" bottomSpacing="3" />

                <div class="flex flex-wrap items-center mb-4">
                    <div class="col flex items-center font-medium">
                        <i class="fa-solid fa-keyboard mr-2 text-slate-500 dark:text-slate-400"></i>
                        {{ __('input') }}
                    </div>
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="inputType" id="inputType1" value="NUMPAD" autocomplete="off" disabled>
                            <label class="input-type-btn rounded-l-lg" for="inputType1">
                                <i class="fa-solid fa-calculator mr-1"></i>
                                {{ __('NumPad') }}
                            </label>

                            <input type="radio" class="btn-check" name="inputType" id="inputType2" value="DARTBOARD" autocomplete="off" checked>
                            <label class="input-type-btn rounded-r-lg" for="inputType2">
                                <i class="fa-solid fa-bullseye mr-1"></i>
                                {{ __('Dartboard') }}
                            </label>
                        </div>
                    </div>
                </div>

                @if(Str::endsWith($dartGameType, '01') && Str::length($dartGameType) == 3)
                    <div class="lobby-card-header" style="margin-top: 1.5rem;">
                        <i class="fa-solid fa-flag-checkered"></i>
                        <h5>{{ __('checkout') }}</h5>
                    </div>
                    <div class="checkout-option">
                        <x-form.input-checkbox attribute="singleOut" label="single out" helptext="last dart may be a single field" isChecked="true" />
                    </div>
                    <div class="checkout-option">
                        <x-form.input-checkbox attribute="doubleOut" label="double out" helptext="last dart may be a double field" isChecked="true" />
                    </div>
                    <div class="checkout-option">
                        <x-form.input-checkbox attribute="trippleOut" label="tripple out" helptext="last dart may be a tripple field" isChecked="true" />
                    </div>
                @elseif($dartGameType == 'cricket')
                    <p class="text-center text-muted py-3">Cricket - {{ __('no settings available') }}</p>
                @else
                    <p class="text-center text-muted py-3">{{ __('no settings available') }}</p>
                @endif
            </section>

            <!-- Selected Players -->
            <section id="dart-game-player" class="lobby-card">
                <div class="lobby-card-header">
                    <i class="fa-solid fa-users"></i>
                    <h5>{{ __('selected player') }}</h5>
                </div>

                <div id="selectedUserList">
                    <div class="player-item">
                        <input type="hidden" name="users[{{ Auth::user()->id }}]" value="{{ Auth::user()->id }}">
                        @if(Auth::user()->img)
                            <img src="{{ Auth::user()->img }}" alt="{{ Auth::user()->name }}">
                        @else
                            {!! Avatar::create(Auth::user()->name)->setDimension(40)->setFontSize(18)->toSvg() !!}
                        @endif
                        <span class="player-name">{{ __('me') }}</span>
                        <div class="actions">
                            <button type="button" class="btn btn-down-player">
                                <i class="fa-solid fa-arrow-down fa-lg"></i>
                            </button>
                            <button type="button" class="btn btn-remove-player">
                                <i class="fa-solid fa-xmark fa-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="lobby-card-header" style="margin-top: 1.5rem;">
                    <i class="fa-solid fa-user-plus"></i>
                    <h5>{{ __('player selection') }}</h5>
                </div>

                <ul class="list-group mb-3" id="ListUser" style="list-style: none; padding: 0;"></ul>

                <div class="search-input-group">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" id="SearchUser" placeholder="{{ __('search') }} {{ __('username') }}...">
                </div>
            </section>

            <!-- Dart Queue -->
            <section id="dart-game-queue" class="lobby-card">
                <div class="lobby-card-header">
                    <i class="fa-solid fa-list-ol"></i>
                    <h5>{{ __('dart queue') }}</h5>
                </div>

                <div id="dartQueueList">
                    @forelse ($dartQueue as $queueItem)
                        <div class="queue-item" data-user-id="{{ $queueItem->parentUser->id }}">
                            @if($queueItem->parentUser->img)
                                <img src="{{ $queueItem->parentUser->img }}" alt="{{ $queueItem->parentUser->name }}">
                            @else
                                {!! Avatar::create($queueItem->parentUser->name)->setDimension(36)->setFontSize(16)->toSvg() !!}
                            @endif
                            <span class="flex-1 font-medium">{{ $queueItem->parentUser->name }}</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">{{ $queueItem->created_at->diffForHumans() }}</span>
                            @if($queueItem->parentUser->hasDartQueueChilds())
                                <button type="button" class="btn btn-add-all-players" title="{{ __('add all') }}">
                                    <i class="fa-solid fa-users fa-lg"></i>
                                </button>
                            @endif
                            <button type="button" class="btn btn-add-player" title="{{ __('add player') }}">
                                <i class="fa-solid fa-plus fa-lg"></i>
                            </button>
                        </div>
                        @if($queueItem->parentUser->hasDartQueueChilds())
                            <div class="ml-8 mb-2">
                                @foreach ($queueItem->parentUser->dartQueueChilds()->get() as $childUser)
                                    <div class="queue-item" data-user-id="{{ $childUser->id }}" style="background: transparent; padding: 0.5rem 1rem;">
                                        @if($childUser->img)
                                            <img src="{{ $childUser->img }}" alt="{{ $childUser->name }}" style="width: 28px; height: 28px;">
                                        @else
                                            {!! Avatar::create($childUser->name)->setDimension(28)->setFontSize(12)->toSvg() !!}
                                        @endif
                                        <span class="flex-1">{{ $childUser->name }}</span>
                                        <button type="button" class="btn btn-add-player" title="{{ __('add player') }}">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @empty
                        <div class="empty-queue-placeholder">
                            <i class="fa-solid fa-inbox fa-2x mb-3" style="opacity: 0.3;"></i>
                            <p class="mb-0">{{ __('no data') }}</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </form>
    </div>
</div>
@endsection
