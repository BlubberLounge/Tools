@extends('layouts.app')

@push('styles')
    @vite(['resources/sass/dartboard.scss'])
    <style>
        .game-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 100px;
            padding: 1.5rem 1rem;
            @apply bg-gradient-to-br from-white to-slate-100 dark:from-slate-800 dark:to-slate-900;
            @apply border-2 border-slate-300 dark:border-slate-700;
            border-radius: 1rem;
            text-decoration: none;
            @apply text-slate-800 dark:text-slate-200;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .game-card:hover {
            transform: translateY(-4px);
            @apply border-orange-500;
            box-shadow: 0 12px 24px rgba(249, 115, 22, 0.2);
            @apply text-orange-500;
        }
        .game-card:hover .game-icon {
            transform: scale(1.1);
            @apply text-orange-500;
        }
        .game-card.featured {
            @apply bg-gradient-to-br from-orange-500 to-orange-600 border-orange-500 text-white;
        }
        .game-card.featured:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(249, 115, 22, 0.4);
            @apply text-white;
        }
        .game-card.featured .game-icon {
            @apply text-white;
        }
        .game-card.disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }
        .game-icon {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }
        .game-label {
            font-weight: 600;
            font-size: 1.1rem;
        }
        .game-badge {
            font-size: 0.65rem;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
            @apply bg-white/20;
            margin-top: 0.5rem;
        }
    </style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">

    <!-- Header -->
    <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4 bg-orange-500/10">
            <i class="fa-solid fa-bullseye text-4xl text-orange-500"></i>
        </div>
        <h1 class="text-3xl font-bold mb-2">{{ __('dart game') }}</h1>
        <p class="max-w-xl mx-auto text-sm text-slate-500 dark:text-slate-400">
            {{ __('darts or dart-throwing is a competitive sport in which two or more players bare-handedly throw small sharp-pointed missiles known as darts at a round target known as a dartboard.') }}
        </p>
    </div>

    <!-- X01 Games -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-center mb-4 text-slate-500 dark:text-slate-400">
            <i class="fa-solid fa-crosshairs mr-2"></i>X01 {{ __('games') }}
        </h2>
        <div class="flex flex-wrap gap-3 justify-center">
            @for($i = 2; $i <= 8; $i++)
                <a href="{{ route('dart.game.create', ['type' => $i.'01']) }}"
                   class="game-card {{ $i == 5 ? 'featured' : '' }}"
                   data-bl-dart-game-type="{{ $i }}01"
                   x-tooltip="'{{ __('popover.dartX01', ['points' => $i.'01']) }}'">
                    <i class="fa-solid fa-crosshairs game-icon"></i>
                    <span class="game-label">{{ $i }}01</span>
                    @if($i == 5)
                        <span class="game-badge">Popular</span>
                    @endif
                </a>
            @endfor
        </div>
    </div>

    <!-- Special Games -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-center mb-4 text-slate-500 dark:text-slate-400">
            <i class="fa-solid fa-star mr-2"></i>{{ __('special modes') }}
        </h2>
        <div class="flex flex-wrap gap-3 justify-center">
            <a href="{{ route('dart.game.create', ['type' => 'aroundTheClock']) }}" class="game-card disabled">
                <i class="fa-solid fa-clock game-icon"></i>
                <span class="game-label">{{ __('around the clock') }}</span>
                <span class="game-badge">Coming Soon</span>
            </a>
            <a href="{{ route('dart.game.create', ['type' => 'cricket']) }}" class="game-card disabled">
                <i class="fa-solid fa-bullseye game-icon"></i>
                <span class="game-label">{{ __('cricket') }}</span>
                <span class="game-badge">Coming Soon</span>
            </a>
            <a href="{{ route('dart.game.create', ['type' => 'practise']) }}" class="game-card disabled">
                <i class="fa-solid fa-graduation-cap game-icon"></i>
                <span class="game-label">{{ __('practise') }}</span>
                <span class="game-badge">Coming Soon</span>
            </a>
        </div>
    </div>

</div>
@endsection
