@extends('layouts.app')

@section('content')
<div class="container mx-auto">

    <div class="mb-6">
        <h1 class="text-xl font-bold flex items-center gap-2">
            <i class="fa-solid fa-cube text-primary"></i>
            {{ __('Minecraft tools') }}
        </h1>
        <p class="text-base-content/60 text-sm">{{ __('Small helpers for Minecraft Java Edition.') }}</p>
    </div>

    @if(count($tools) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($tools as $tool)
                <a href="{{ route($tool['route']) }}" class="card bg-base-100 shadow-sm hover:shadow-md hover:border-primary border border-base-300 transition" data-minecraft-tool="{{ $tool['id'] }}">
                    <div class="card-body flex-row items-start gap-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-box bg-primary/10 text-primary text-xl shrink-0">
                            <i class="{{ $tool['icon'] }}"></i>
                        </div>
                        <div>
                            <h2 class="card-title text-base">{{ __($tool['title']) }}</h2>
                            <p class="text-sm text-base-content/60">{{ __($tool['description']) }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="alert">
            <i class="fa-solid fa-circle-info"></i>
            <span>{{ __('No Minecraft tools are available for your account.') }}</span>
        </div>
    @endif

</div>
@endsection
