<nav id="sidebar" class="p-1 bg-[var(--tw-body-tertiary-bg)] active">
    <div class="flex flex-col shrink-0 h-full">
        <div class="flex justify-center py-1">
            <a class="flex items-center" href="{{ url('/') }}">
                <img src="https://media.blubber-lounge.de/images/blubber_lounge_rebrand_try_white_optimized.svg" class="nav-brand" alt="BlubberLounge Logo" width="80px">
            </a>
            <div class="vertical-divider"></div>
            <a class="nav-brand-sub flex items-center text-base" href="{{ url('/') }}">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </a>
        </div>

        <hr class="mb-1 border-[var(--tw-border-color)]" />

        <ul class="nav nav-pills flex-col mb-auto">
            <li class="nav-item">
                <a href="{{ route('home') }}" class="nav-link {{ active('home') }}">
                    <i class="fa-solid fa-house"></i>
                    <span class="nav-text"> {{ __('home') }} </span>
                </a>
            </li>
            @permission('viewany.appointment')
                <li class="nav-item">
                    <a href="{{ route('appointment.index') }}" class="nav-link {{ active('appointment.index') }}">
                        <i class="fa-solid fa-ticket"></i>
                        <span class="nav-text"> {{ __('events') }} </span>
                    </a>
                </li>
            @endpermission
            @permission('viewany.dart.game')
                <li class="nav-item">
                    <a href="{{ route('dart.game.index') }}" class="nav-link {{ active('dart.game.index') }}">
                        <i class="fa-solid fa-bullseye"></i>
                        <span class="nav-text"> {{ __('dart game') }} </span>
                    </a>
                </li>
            @endpermission
            @permission('viewany.dart')
                <li class="nav-item">
                    <a href="{{ route('dart.index') }}" class="nav-link {{ active('dart.index') }}">
                        <i class="fa-solid fa-chart-simple"></i>
                        <span class="nav-text"> {{ __('dart dashboard') }} </span>
                    </a>
                </li>
            @endpermission
            @if(Auth::user()->hasPermission(['view.dart.infos', 'view.dart.checkouts', 'view.dart.playground']))
                <li class="nav-item" x-data="{ open: {{ active(['dart.show-info', 'dart.show-checkout-calculator']) ? 'true' : 'false' }} }">
                    <a href="#" @click.prevent="open = !open" class="nav-link has-submenu">
                        <i class="fa-solid fa-hashtag"></i>
                        <span class="nav-text"> {{ __('dart extras') }} </span>
                    </a>
                    <ul x-show="open" x-collapse id="submenuDart" class="submenu">
                        @permission('view.dart.info')
                            <li class="submenu-item">
                                <a href="{{ route('dart.show-info') }}" class="nav-link submenu-link {{ active('dart.show-info') }}">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span class="nav-text"> {{ __('dart information') }} </span>
                                </a>
                            </li>
                        @endpermission
                        @permission('view.dart.checkouts')
                            <li class="submenu-item">
                                <a href="{{ route('dart.show-checkout-calculator') }}" class="nav-link submenu-link {{ active('dart.show-checkout-calculator') }}">
                                    <i class="fa-solid fa-chart-pie"></i>
                                    <span class="nav-text"> {{ __('dart checkout calculator') }} </span>
                                </a>
                            </li>
                        @endpermission
                        @permission('view.dart.playground')
                            <li class="submenu-item">
                                <a href="{{ route('dart.show-playground') }}" class="nav-link submenu-link {{ active('dart.show-playground') }}">
                                    <i class="fa-solid fa-bug"></i>
                                    <span class="nav-text"> {{ __('dart playground') }} <small>(beta)</small> </span>
                                </a>
                            </li>
                        @endpermission
                    </ul>
                </li>
            @endif
            @if(Auth::user()->hasPermission(['viewany.cocktail']))
                <li class="nav-item" x-data="{ open: {{ active(['cocktail.index']) ? 'true' : 'false' }} }">
                    <a href="#" @click.prevent="open = !open" class="nav-link has-submenu">
                        <i class="fas fa-cocktail"></i>
                        <span class="nav-text"> {{ __('cocktails') }} </span>
                    </a>
                    <ul x-show="open" x-collapse id="submenuCocktail" class="submenu">
                        @permission('viewany.cocktail')
                            <li class="submenu-item">
                                <a href="{{ route('cocktail.index') }}" class="nav-link submenu-link {{ active('cocktail.index') }}">
                                    <i class="fas fa-cocktail"></i>
                                    <span class="nav-text"> {{ __('all') }} </span>
                                </a>
                            </li>
                        @endpermission
                    </ul>
                </li>
            @endif
            @permission('view.calculator')
                <li class="nav-item" x-data="{ open: false }">
                    <a href="#" @click.prevent="open = !open" class="nav-link has-submenu disabled">
                        <i class="fa-solid fa-calculator"></i>
                        <span class="nav-text"> {{ __('calculators') }} </span>
                    </a>
                    <ul x-show="open" x-collapse class="submenu" id="submenuCalculators">
                        <li class="submenu-item">
                            <a href="#" class="nav-link submenu-link disabled">
                                {{ __('coal calculator') }}
                            </a>
                        </li>
                        <li class="submenu-item">
                            <a href="#" class="nav-link submenu-link disabled">
                                {{ __('tobacco calculator') }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endpermission
            @permission('view.battery')
                <li class="nav-item">
                    <a href="{{ route('battery') }}" class="nav-link {{ active('battery') }}">
                        <i class="fa-solid fa-battery-three-quarters fa-rotate-270"></i>
                        <span class="nav-text"> {{ __('battery simulation') }} </span>
                    </a>
                </li>
            @endpermission
            @permission('view.moving.averages')
                <li class="nav-item">
                    <a href="{{ route('show-moving-average') }}" class="nav-link {{ active('show-moving-average') }}">
                        <i class="fa-solid fa-person-running"></i>
                        <span class="nav-text"> {{ __('moving average') }} </span>
                    </a>
                </li>
            @endpermission
            @permission('view.airsoft')
                <li class="nav-item">
                    <a href="{{ route('show-airsoft-calculator') }}" class="nav-link {{ active('show-airsoft-calculator') }}">
                        <i class="fa-solid fa-gun"></i>
                        <span class="nav-text"> {{ __('airsoft calculator') }} </span>
                    </a>
                </li>
            @endpermission
            @permission('view.iec7064')
                <li class="nav-item">
                    <a href="{{ route('show-iec7064') }}" class="nav-link {{ active('show-iec7064') }}">
                        <i class="fa-solid fa-globe"></i>
                        <span class="nav-text"> {{ __('IEC 7064') }} </span>
                    </a>
                </li>
            @endpermission
        </ul>

        <hr class="border-[var(--tw-border-color)] my-1" />

        <ul class="nav nav-pills nav-sm flex-col">
            <li class="nav-item">
                <a href="#" class="flex items-center py-1">
                    <img src="{{ Auth::user()->img }}" width="32" class="rounded-full mr-2">
                    <strong class="text-xs"> {{ Auth::user()->full_name }} </strong>
                </a>
            </li>
            <li class="nav-item mt-1">
                <a href="{{ route('user.settings') }}" class="nav-link {{ active('user.settings') }}">
                    <i class="fa-solid fa-gears"></i>
                    <span class="nav-text"> {{ __('settings') }} </span>
                </a>
            </li>
            @permission('viewany.device')
                <li class="nav-item">
                    <a href="{{ route('device.index') }}" class="nav-link {{ active('device.index') }}">
                        <i class="fa-solid fa-desktop"></i>
                        <span class="nav-text"> {{ __('devices') }} </span>
                    </a>
                </li>
            @endpermission
            @permission('create.feedback')
                <li class="nav-item">
                    <a href="{{ route('feedback.create') }}" class="nav-link {{ request()->routeIs('feedback.create') ? 'active' : '' }}" >
                        <i class="fa-solid fa-circle-question"></i>
                        <span class="nav-text"> {{ __('feedback') }} </span>
                    </a>
                </li>
            @endpermission
            @permission('viewany.f.a.q')
                <li class="nav-item">
                    <a href="{{ route('faq.index') }}" class="nav-link {{ active('faq.*') }}" >
                        <i class="fa-solid fa-comment-dots"></i>
                        <span class="nav-text"> {{ __('tools FAQ') }} </span>
                    </a>
                </li>
            @endpermission
            @level(5)
                <li class="nav-item mt-2" x-data="{ open: false }">
                    <a href="#" @click.prevent="open = !open" class="nav-link has-submenu">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span class="nav-text"> {{ __('administration') }} </span>
                    </a>
                    <ul x-show="open" x-collapse class="submenu" id="submenuAdministration">
                        <li class="submenu-item">
                            <a href="{{ route('audit-log.index') }}" class="nav-link submenu-link">
                                <i class="fa-solid fa-list-ul"></i>
                                <span class="nav-text"> {{ __('audit log') }} </span>
                            </a>
                        </li>
                        <li class="submenu-item">
                            <a href="{{ route('invitation.index') }}" class="nav-link submenu-link">
                                <i class="fa-regular fa-envelope"></i>
                                <span class="nav-text"> {{ __('access requests') }} </span>
                            </a>
                        </li>
                        <li class="submenu-item">
                            <a href="{{ route('user.index') }}" class="nav-link submenu-link">
                                <i class="fa-solid fa-users"></i>
                                <span class="nav-text"> {{ __('user management') }} </span>
                            </a>
                        </li>
                        <li class="submenu-item">
                            <a href="{{ route('feedback.index') }}" class="nav-link submenu-link">
                                <i class="fa-solid fa-circle-question"></i>
                                <span class="nav-text"> {{ __('user feedback') }} </span>
                            </a>
                        </li>
                        <li class="submenu-item">
                            <a href="{{ route('l5-swagger.default.api') }}" class="nav-link submenu-link">
                                <i class="fa-solid fa-users"></i>
                                <span class="nav-text"> {{ __('api documentation') }} </span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endlevel
            <li class="nav-item mt-1">
                <a href="{{ route('logout') }}" class="nav-link text-danger" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="nav-text"> {{ __('logout') }} </span>
                </a>
            </li>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </ul>

        <hr class="border-[var(--tw-border-color)] my-1" />

        <div class="flex btn-group text-xs" id="bd-theme">
            <button type="button" class="btn btn-dark btn-sm flex items-center flex-1 px-1 py-0.5" data-theme-value="light">
                <i class="mr-1 fa-solid fa-sun opacity-50 theme-icon text-xs"></i>
                {{ __('Light') }}
            </button>
            <button type="button" class="btn btn-dark btn-sm flex items-center flex-1 px-1 py-0.5" data-theme-value="dark">
                <i class="mr-1 fa-solid fa-moon opacity-50 theme-icon text-xs"></i>
                {{ __('Dark') }}
            </button>
            <button type="button" class="btn btn-dark btn-sm flex items-center flex-1 px-1 py-0.5 active" data-theme-value="auto">
                <i class="mr-1 fa-solid fa-circle-half-stroke opacity-50 theme-icon text-xs"></i>
                {{ __('Auto') }}
            </button>
        </div>
        @permission('update.user.language')
            @if(config('app.available_locales'))
                <form id="form-locale-selector" action="{{ route('user.language-update') }}" method="POST" class="mt-1">
                    @csrf
                    @method('PUT')
                    <div class="flex btn-group btn-sm w-full" id="language-selector">
                        @foreach (config('app.available_locales') as $k => $locale)
                            <input id="locale_{{ $locale }}" class="sr-only peer" type="radio" name="locale" value="{{ $locale }}" @checked($locale == App::currentLocale())>
                            <label class="btn btn-dark btn-sm flex-1 px-1 py-0.5 peer-checked:bg-secondary" for="locale_{{ $locale }}">
                                <span class="fi fi-{{ $locale === 'en' ? 'gb' : $locale}}"></span>
                            </label>
                        @endforeach
                    </div>
                </form>
            @endif
        @endpermission
    </div>

    <div class="mt-2">
        <div class="p-0 text-center">
            <a href="#" class="mr-1 text-xs link-secondary hover:underline"> {{ __('about') }} </a>
            <a href="#" class="mr-1 text-xs link-secondary hover:underline"> {{ __('changelog') }} </a>
            <a href="#" class="text-xs link-secondary hover:underline"> {{ __('contact') }} </a>
        </div>
        <div class="p-0 text-center">
            <a href="#" class="mr-1 text-xs link-secondary hover:underline"> {{ __('impressum') }} </a>
            <a href="#" class="text-xs link-secondary hover:underline"> {{ __('privacy') }} </a>
        </div>
    </div>

    <div class="text-center mt-1 pb-1">
        <p class="m-0 text-xs" style="color:var(--bl-clr-gray-60);">Tools v{{ env('APP_VERSION', 'VERSION_invalid') }}-{{ Str::upper(env('APP_ENV', 'ENV_invalid')) }}</p>
        <p class="m-0 text-xs" style="color:var(--bl-clr-gray-70);"><i class="fa-regular fa-copyright fa-xs"></i> BlubberLounge {{ now()->year }}</p>
    </div>
</nav>
