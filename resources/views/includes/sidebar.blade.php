<nav class="bg-base-100 w-56 min-h-full flex flex-col p-3 custom-scrollbar overflow-y-auto border-r border-base-300">
    {{-- Logo --}}
    <div class="flex justify-center items-center py-3 mb-2">
        <a class="flex items-center hover:opacity-80 transition-opacity" href="{{ url('/') }}">
            <img src="https://media.blubber-lounge.de/images/blubber_lounge_rebrand_try_white_optimized.svg" class="nav-brand" alt="BlubberLounge Logo" width="90px">
        </a>
        <div class="divider divider-horizontal mx-1 h-8"></div>
        <a class="nav-brand-sub flex items-center text-xl hover:text-primary transition-colors" href="{{ url('/') }}">
            <i class="fa-solid fa-screwdriver-wrench"></i>
        </a>
    </div>

    <div class="divider my-0"></div>

    {{-- Navigation --}}
    <ul class="menu gap-0.5 flex-nowrap flex-1 p-0 pt-2">
        <li><a href="{{ route('home') }}" class="{{ active('home') }}"><i class="fa-solid fa-house w-4 text-center"></i>{{ __('home') }}</a></li>

        @permission('viewany.appointment')
            <li><a href="{{ route('appointment.index') }}" class="{{ active('appointment.index') }}"><i class="fa-solid fa-ticket w-4 text-center"></i>{{ __('events') }}</a></li>
        @endpermission
        @permission('viewany.dart.game')
            <li><a href="{{ route('dart.game.index') }}" class="{{ active('dart.game.index') }}"><i class="fa-solid fa-bullseye w-4 text-center"></i>{{ __('dart game') }}</a></li>
        @endpermission
        @permission('viewany.dart')
            <li><a href="{{ route('dart.index') }}" class="{{ active('dart.index') }}"><i class="fa-solid fa-chart-simple w-4 text-center"></i>{{ __('dart dashboard') }}</a></li>
        @endpermission

        @if(Auth::user()->hasPermission(['view.dart.infos', 'view.dart.checkouts', 'view.dart.playground']))
            <li>
                <details {{ active(['dart.show-info', 'dart.show-checkout-calculator']) ? 'open' : '' }}>
                    <summary><i class="fa-solid fa-hashtag w-4 text-center"></i>{{ __('dart extras') }}</summary>
                    <ul>
                        @permission('view.dart.info')
                            <li><a href="{{ route('dart.show-info') }}" class="{{ active('dart.show-info') }}"><i class="fa-solid fa-circle-info w-4 text-center"></i>{{ __('dart information') }}</a></li>
                        @endpermission
                        @permission('view.dart.checkouts')
                            <li><a href="{{ route('dart.show-checkout-calculator') }}" class="{{ active('dart.show-checkout-calculator') }}"><i class="fa-solid fa-chart-pie w-4 text-center"></i>{{ __('dart checkout calculator') }}</a></li>
                        @endpermission
                        @permission('view.dart.playground')
                            <li><a href="{{ route('dart.show-playground') }}" class="{{ active('dart.show-playground') }}"><i class="fa-solid fa-bug w-4 text-center"></i>{{ __('dart playground') }} <span class="badge badge-xs">beta</span></a></li>
                        @endpermission
                    </ul>
                </details>
            </li>
        @endif

        {{-- Minecraft: entries come from config/minecraft.php (App\Minecraft\ToolRegistry) --}}
        @php($minecraftTools = \App\Minecraft\ToolRegistry::visibleTo(Auth::user()))
        @if(Auth::user()->hasPermission(\App\Minecraft\ToolRegistry::SECTION_PERMISSION) || count($minecraftTools) > 0)
            <li>
                <details {{ active(\App\Minecraft\ToolRegistry::routeNames()) ? 'open' : '' }}>
                    <summary><i class="fa-solid fa-cube w-4 text-center"></i>{{ __('minecraft') }}</summary>
                    <ul>
                        @permission(\App\Minecraft\ToolRegistry::SECTION_PERMISSION)
                            <li><a href="{{ route('minecraft.index') }}" class="{{ active('minecraft.index') }}"><i class="fa-solid fa-table-cells-large w-4 text-center"></i>{{ __('minecraft tools') }}</a></li>
                        @endpermission
                        @foreach($minecraftTools as $tool)
                            <li><a href="{{ route($tool['route']) }}" class="{{ active($tool['route']) }}"><i class="{{ $tool['icon'] }} w-4 text-center"></i>{{ __($tool['title']) }}</a></li>
                        @endforeach
                    </ul>
                </details>
            </li>
        @endif

        @permission('view.battery')
            <li><a href="{{ route('battery') }}" class="{{ active('battery') }}"><i class="fa-solid fa-battery-three-quarters fa-rotate-270 w-4 text-center"></i>{{ __('battery simulation') }}</a></li>
        @endpermission
        @permission('view.moving.averages')
            <li><a href="{{ route('show-moving-average') }}" class="{{ active('show-moving-average') }}"><i class="fa-solid fa-person-running w-4 text-center"></i>{{ __('moving average') }}</a></li>
        @endpermission
        @permission('view.airsoft')
            <li><a href="{{ route('show-airsoft-calculator') }}" class="{{ active('show-airsoft-calculator') }}"><i class="fa-solid fa-gun w-4 text-center"></i>{{ __('airsoft calculator') }}</a></li>
        @endpermission
        @permission('view.iec7064')
            <li><a href="{{ route('show-iec7064') }}" class="{{ active('show-iec7064') }}"><i class="fa-solid fa-globe w-4 text-center"></i>{{ __('IEC 7064') }}</a></li>
        @endpermission
    </ul>

    {{-- Bottom: Theme + Language --}}
    <div class="mt-auto pt-2">
        <div class="divider my-1"></div>
        <div class="join w-full mb-1" id="bd-theme">
            <button type="button" class="join-item btn btn-sm flex-1" data-theme-value="light" title="Light"><i class="fa-solid fa-sun"></i></button>
            <button type="button" class="join-item btn btn-sm flex-1" data-theme-value="dark" title="Dark"><i class="fa-solid fa-moon"></i></button>
            <button type="button" class="join-item btn btn-sm flex-1" data-theme-value="auto" title="Auto"><i class="fa-solid fa-circle-half-stroke"></i></button>
        </div>
        @permission('update.user.language')
            @if(config('app.available_locales'))
                <form id="form-locale-selector" action="{{ route('user.language-update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="join w-full" id="language-selector">
                        @foreach (config('app.available_locales') as $k => $locale)
                            <input id="locale_{{ $locale }}" class="hidden peer/{{ $locale }}" type="radio" name="locale" value="{{ $locale }}" @checked($locale == App::currentLocale())>
                            <label class="join-item btn btn-sm flex-1 peer-checked/{{ $locale }}:btn-primary" for="locale_{{ $locale }}">
                                <span class="fi fi-{{ $locale === 'en' ? 'gb' : $locale }}"></span>
                            </label>
                        @endforeach
                    </div>
                </form>
            @endif
        @endpermission
        <div class="text-center text-xs text-base-content/30 mt-3">
            <span class="font-semibold">Tools</span> v{{ env('APP_VERSION', 'VERSION_invalid') }}
        </div>
    </div>
</nav>
