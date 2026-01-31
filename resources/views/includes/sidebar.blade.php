<nav id="sidebar" class="active">
    <div class="flex flex-col shrink-0 h-full">
        <!-- Logo Section -->
        <div class="flex justify-center items-center py-3 mb-2">
            <a class="flex items-center hover:opacity-80 transition-opacity" href="{{ url('/') }}">
                <img src="https://media.blubber-lounge.de/images/blubber_lounge_rebrand_try_white_optimized.svg" class="nav-brand" alt="BlubberLounge Logo" width="90px">
            </a>
            <div class="vertical-divider mx-3 h-8"></div>
            <a class="nav-brand-sub flex items-center text-xl hover:text-primary transition-colors" href="{{ url('/') }}">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </a>
        </div>

        <hr class="mb-3 border-[var(--tw-border-color)] opacity-50" />

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

        <hr class="border-[var(--tw-border-color)] my-2 opacity-50" />

        <!-- User Section (compact) -->
        <div class="flex items-center px-2 py-1 mb-1">
            <img src="{{ Auth::user()->img }}" width="28" class="rounded-full ring-2 ring-primary/30">
            <span class="ml-2 text-xs font-medium truncate">{{ Auth::user()->full_name }}</span>
        </div>

        <ul class="nav nav-pills nav-sm flex-col text-xs">
            <li><a href="{{ route('user.settings') }}" class="nav-link py-1 {{ active('user.settings') }}"><i class="fa-solid fa-gears"></i><span class="nav-text">{{ __('settings') }}</span></a></li>
            @permission('viewany.device')
                <li><a href="{{ route('device.index') }}" class="nav-link py-1 {{ active('device.index') }}"><i class="fa-solid fa-desktop"></i><span class="nav-text">{{ __('devices') }}</span></a></li>
            @endpermission
            @permission('create.feedback')
                <li><a href="{{ route('feedback.create') }}" class="nav-link py-1 {{ request()->routeIs('feedback.create') ? 'active' : '' }}"><i class="fa-solid fa-circle-question"></i><span class="nav-text">{{ __('feedback') }}</span></a></li>
            @endpermission
            @permission('viewany.f.a.q')
                <li><a href="{{ route('faq.index') }}" class="nav-link py-1 {{ active('faq.*') }}"><i class="fa-solid fa-comment-dots"></i><span class="nav-text">{{ __('tools FAQ') }}</span></a></li>
            @endpermission
            @level(5)
                <li class="mt-1" x-data="{ open: false }">
                    <a href="#" @click.prevent="open = !open" class="nav-link py-1 has-submenu"><i class="fa-solid fa-shield-halved"></i><span class="nav-text">{{ __('administration') }}</span></a>
                    <ul x-show="open" x-collapse class="submenu" id="submenuAdministration">
                        <li><a href="{{ route('audit-log.index') }}" class="nav-link submenu-link py-0.5"><i class="fa-solid fa-list-ul"></i><span class="nav-text">{{ __('audit log') }}</span></a></li>
                        <li><a href="{{ route('invitation.index') }}" class="nav-link submenu-link py-0.5"><i class="fa-regular fa-envelope"></i><span class="nav-text">{{ __('access requests') }}</span></a></li>
                        <li><a href="{{ route('user.index') }}" class="nav-link submenu-link py-0.5"><i class="fa-solid fa-users"></i><span class="nav-text">{{ __('user management') }}</span></a></li>
                        <li><a href="{{ route('feedback.index') }}" class="nav-link submenu-link py-0.5"><i class="fa-solid fa-circle-question"></i><span class="nav-text">{{ __('user feedback') }}</span></a></li>
                        <li><a href="{{ route('l5-swagger.default.api') }}" class="nav-link submenu-link py-0.5"><i class="fa-solid fa-book"></i><span class="nav-text">{{ __('api documentation') }}</span></a></li>
                    </ul>
                </li>
            @endlevel
            <li class="mt-1"><a href="{{ route('logout') }}" class="nav-link py-1 text-danger" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i class="fa-solid fa-right-from-bracket"></i><span class="nav-text">{{ __('logout') }}</span></a></li>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        </ul>

        <hr class="border-[var(--tw-border-color)] my-2 opacity-50" />

        <!-- Theme & Language Toggle -->
        <div class="flex gap-1 mb-1" id="bd-theme">
            <button type="button" class="flex-1 py-1.5 text-xs rounded bg-[var(--tw-body-bg)] hover:bg-[var(--tw-border-color)] transition-colors" data-theme-value="light" title="Light">
                <i class="fa-solid fa-sun text-yellow-500"></i>
            </button>
            <button type="button" class="flex-1 py-1.5 text-xs rounded bg-[var(--tw-body-bg)] hover:bg-[var(--tw-border-color)] transition-colors" data-theme-value="dark" title="Dark">
                <i class="fa-solid fa-moon text-blue-400"></i>
            </button>
            <button type="button" class="flex-1 py-1.5 text-xs rounded bg-[var(--tw-body-bg)] hover:bg-[var(--tw-border-color)] transition-colors" data-theme-value="auto" title="Auto">
                <i class="fa-solid fa-circle-half-stroke text-purple-400"></i>
            </button>
        </div>
        @permission('update.user.language')
            @if(config('app.available_locales'))
                <form id="form-locale-selector" action="{{ route('user.language-update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="flex gap-1" id="language-selector">
                        @foreach (config('app.available_locales') as $k => $locale)
                            <input id="locale_{{ $locale }}" class="hidden peer/{{ $locale }}" type="radio" name="locale" value="{{ $locale }}" @checked($locale == App::currentLocale())>
                            <label class="flex-1 py-1.5 text-center text-xs rounded cursor-pointer bg-[var(--tw-body-bg)] hover:bg-[var(--tw-border-color)] peer-checked/{{ $locale }}:bg-primary peer-checked/{{ $locale }}:text-white transition-colors" for="locale_{{ $locale }}">
                                <span class="fi fi-{{ $locale === 'en' ? 'gb' : $locale }}"></span>
                            </label>
                        @endforeach
                    </div>
                </form>
            @endif
        @endpermission
    </div>

    <!-- Footer (compact) -->
    <div class="mt-auto pt-2 text-center text-xs text-[var(--tw-muted-color)]">
        <div class="flex justify-center gap-2 flex-wrap">
            <a href="#" class="hover:text-primary">{{ __('about') }}</a>
            <a href="#" class="hover:text-primary">{{ __('impressum') }}</a>
            <a href="#" class="hover:text-primary">{{ __('privacy') }}</a>
        </div>
        <p class="mt-1 mb-0 opacity-70">
            <span class="text-primary font-semibold">Tools</span> v{{ env('APP_VERSION', 'VERSION_invalid') }}
        </p>
    </div>
</nav>
