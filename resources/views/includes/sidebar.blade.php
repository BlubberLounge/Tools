<nav id="sidebar" class="p-2 bg-body-tertiary active">
    <div class="d-flex flex-column" style="flex-shrink:0;min-height:100%;">
        <div class="d-flex justify-center py-2">
            <a class="d-flex align-items-center" href="{{ url('/') }}">
                <img src="https://media.maximilian-mewes.de/project/bl/blubber_lounge_rebrand_try_white_optimized.svg" class="nav-brand" alt="BlubberLounge Logo" width="100px">
            </a>
            <div class="vertical-divider"></div>
            <a class="nav-brand-sub d-flex align-items-center" href="{{ url('/') }}">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </a>
        </div>

        <hr class="mb-2" />

        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('home') }}" class="nav-link {{ active('home') }}">
                    <i class="fa-solid fa-house"></i>
                    <span class="nav-text"> {{ __('home') }} <span>
                </a>
            </li>
            @permission('viewany.dart.game')
                <li class="nav-item">
                    <a href="{{ route('dart.game.index') }}" class="nav-link {{ active('dart.game.index') }}">
                        <i class="fa-solid fa-bullseye"></i>
                        <span class="nav-text"> {{ __('dart game') }} <span>
                    </a>
                </li>
            @endpermission
            @permission('view.dart.game.live')
                <li class="nav-item">
                    <a href="{{ route('dart.game.live') }}" class="nav-link {{ active('dart.game.live') }}">
                        <i class="fa-solid fa-satellite-dish"></i>
                        <span class="nav-text"> {{ __('dart live') }} <span>
                    </a>
                </li>
            @endpermission
            @permission('viewany.dart')
                <li class="nav-item">
                    <a href="{{ route('dart.index') }}" class="nav-link {{ active('dart.index') }}">
                        <i class="fa-solid fa-chart-simple"></i>
                        <span class="nav-text"> {{ __('dart dashboard') }} <span>
                    </a>
                </li>
            @endpermission
            @if(Auth::user()->hasPermission(['view.dart.infos', 'view.dart.checkouts', 'view.dart.playground']))
                <li class="nav-item">
                    <a href="{{ route('dart.index') }}" class="nav-link has-submenu" data-bs-toggle="collapse" data-bs-target="#submenuDart">
                        <i class="fa-solid fa-hashtag"></i>
                        <span class="nav-text"> {{ __('dart extras') }} <span>
                    </a>
                    <ul id="submenuDart" class="{{ active(['dart.show-info', 'dart.show-checkout-calculator']) ?: 'collapse' }} submenu">
                        @permission('view.dart.info')
                            <li class="submenu-item">
                                <a href="{{ route('dart.show-info') }}" class="nav-link submenu-link {{ active('dart.show-info') }}">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span class="nav-text"> {{ __('dart information') }} <span>
                                </a>
                            </li>
                        @endpermission
                        @permission('view.dart.checkouts')
                            <li class="submenu-item">
                                <a href="{{ route('dart.show-checkout-calculator') }}" class="nav-link submenu-link {{ active('dart.show-checkout-calculator') }}">
                                    <i class="fa-solid fa-chart-pie"></i>
                                    <span class="nav-text"> {{ __('dart checkout calculator') }} <span>
                                </a>
                            </li>
                        @endpermission
                        @permission('view.dart.playground')
                            <li class="submenu-item">
                                <a href="{{ route('dart.show-playground') }}" class="nav-link submenu-link {{ active('dart.show-playground') }}">
                                    <i class="fa-solid fa-bug"></i>
                                    <span class="nav-text"> {{ __('dart playground') }} <small>(beta)</small> <span>
                                </a>
                            </li>
                        @endpermission
                    </ul>
                </li>
            @endif
            @permission('viewany.hookah')
                <li class="nav-item">
                    <a class="nav-link disabled" href="#">
                        <i class="fa-solid fa-bong"></i>
                        <span class="nav-text"> {{ __('Hookahs') }} <span>
                    </a>
                </li>
            @endpermission
            @permission('viewany.tobacco')
                <li class="nav-item">
                    <a href="#" class="nav-link disabled">
                        <i class="fa-solid fa-box-open"></i>
                        <span class="nav-text"> {{ __('Tobaccos') }} <span>
                    </a>
                </li>
            @endpermission
            @permission('view.calculator')
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link has-submenu disabled" data-bs-toggle="collapse" data-bs-target="#submenuCalculators">
                        <i class="fa-solid fa-calculator"></i>
                        <span class="nav-text"> {{ __('calculators') }} <span>
                    </a>
                    <ul class="collapse submenu" id="submenuCalculators">
                        <li class="submenu-item ">
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
                        <span class="nav-text"> {{ __('battery simulation') }} <span>
                    </a>
                </li>
            @endpermission
            @permission('view.moving.averages')
                <li class="nav-item">
                    <a href="{{ route('show-moving-average') }}" class="nav-link {{ active('show-moving-average') }}">
                        <i class="fa-solid fa-person-running"></i>
                        <span class="nav-text"> {{ __('moving average') }} <span>
                    </a>
                </li>
            @endpermission
        </ul>

        <hr />

        <ul class="nav nav-pills nav-sm flex-column">
            <li class="nav-item">
                <a href="#" style="display: flex;align-items: center;">
                    <img src="{{ Auth::user()->img }}" width="48" class="rounded-circle me-2">
                    <strong> {{ Auth::user()->full_name }} </strong>
                </a>
            </li>
            <li class="nav-item mt-2">
                <a href="{{ route('user.settings') }}" class="nav-link {{ active('user.settings') }}">
                    <i class="fa-solid fa-gears"></i>
                    <span class="nav-text"> {{ __('settings') }} <span>
                </a>
            </li>
            @permission('viewany.device')
                <li class="nav-item">
                    <a href="{{ route('device.index') }}" class="nav-link {{ active('device.index') }}">
                        <i class="fa-solid fa-desktop"></i>
                        <span class="nav-text"> {{ __('devices') }} <span>
                    </a>
                </li>
            @endpermission
            @permission('create.feedback')
                <li class="nav-item">
                    <a href="{{ route('feedback.create') }}" class="nav-link {{ request()->routeIs('feedback.create') ? 'active' : '' }}" >
                        <i class="fa-solid fa-circle-question"></i>
                        <span class="nav-text"> {{ __('feedback') }} <span>
                    </a>
                </li>
            @endpermission
            @permission('viewany.f.a.q')
                <li class="nav-item">
                    <a href="{{ route('faq.index') }}" class="nav-link {{ active('faq.*') }}" >
                        <i class="fa-solid fa-comment-dots"></i>
                        <span class="nav-text"> {{ __('tools FAQ') }} <span>
                    </a>
                </li>
            @endpermission
            @level(5)
                <li class="nav-item mt-2">
                    <a href="#" class="nav-link has-submenu" data-bs-toggle="collapse" data-bs-target="#submenuAdministration">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span class="nav-text"> {{ __('administration') }} <span>
                    </a>
                    <ul class="collapse submenu" id="submenuAdministration">
                        <li class="submenu-item">
                            <a href="{{ route('audit-log.index') }}" class="nav-link submenu-link">
                                <i class="fa-solid fa-list-ul"></i>
                                <span class="nav-text"> {{ __('audit log') }} <span>
                            </a>
                        </li>
                        <li class="submenu-item">
                            <a href="{{ route('invitation.index') }}" class="nav-link submenu-link">
                                <i class="fa-regular fa-envelope"></i>
                                <span class="nav-text"> {{ __('access requests') }} <span>
                            </a>
                        </li>
                        <li class="submenu-item">
                            <a href="{{ route('user.index') }}" class="nav-link submenu-link">
                                <i class="fa-solid fa-users"></i>
                                <span class="nav-text"> {{ __('user management') }} <span>
                            </a>
                        </li>
                        <li class="submenu-item">
                            <a href="{{ route('feedback.index') }}" class="nav-link submenu-link">
                                <i class="fa-solid fa-circle-question"></i>
                                <span class="nav-text"> {{ __('user feedback') }} <span>
                            </a>
                        </li>
                        <li class="submenu-item">
                            <a href="{{ route('l5-swagger.default.api') }}" class="nav-link submenu-link">
                                <i class="fa-solid fa-users"></i>
                                <span class="nav-text"> {{ __('api documentation') }} <span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endlevel
            <li class="nav-item mt-3">
                <a href="{{ route('logout') }}" class="nav-link text-danger" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="nav-text"> {{ __('logout') }} <span>
                </a>
            </li>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </ul>

        <hr />

        <div class="btn-group btn-group-sm" id="bd-theme">
            <button type="button" class="btn btn-dark d-flex align-items-center" data-bs-theme-value="light">
                <i class="bi me-2 fa-solid fa-sun opacity-50 theme-icon" data-bs-theme-icon="fa-sun"></i>
                {{ __('Light') }}
                <i class="bi ms-auto d-none fa-solid fa-check"></i>
            </button>
            <button type="button" class="btn btn-dark d-flex align-items-center" data-bs-theme-value="dark">
                <i class="bi me-2 fa-solid fa-moon opacity-50 theme-icon" data-bs-theme-icon="fa-moon"></i>
                {{ __('Dark') }}
                <i class="bi ms-auto d-none fa-solid fa-check"></i>
            </button>
            <button type="button" class="btn btn-dark d-flex align-items-center active" data-bs-theme-value="auto">
                <i class="bi me-2 fa-solid fa-circle-half-stroke opacity-50 theme-icon" data-bs-theme-icon="fa-circle-half-stroke"></i>
                {{ __('Auto') }}
                <i class="bi ms-auto d-none fa-solid fa-check"></i>
            </button>
        </div>
        @permission('update.user.language')
            @if(config('app.available_locales'))
                <form id="form-locale-selector" action="{{ route('user.language-update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="btn-group btn-group-sm w-100" id="language-selector">
                        @foreach (config('app.available_locales') as $k => $locale)
                            <input id="locale_{{ $locale }}" class="btn-check" type="radio" name="locale" value="{{ $locale }}" @checked($locale == App::currentLocale())>
                            <label class="btn btn-dark" for="locale_{{ $locale }}">
                                <span class="fi fi-{{ $locale === 'en' ? 'gb' : $locale}}"></span>
                            </label>
                        @endforeach
                    </div>
                </form>
            @endif
        @endpermission
    </div>

    <div class="row m-0 mt-5">
        <div class="col-12 p-0 text-center">
            <a href="" class="me-2 small link-secondary link-offset-2 link-underline-opacity-50 link-underline-opacity-100-hover"> {{ __('about') }} </a>
            <a href="" class="me-2 small link-secondary link-offset-2 link-underline-opacity-50 link-underline-opacity-100-hover"> {{ __('changelog') }} </a>
            <a href="" class="me-2 small link-secondary link-offset-2 link-underline-opacity-50 link-underline-opacity-100-hover"> {{ __('contact') }} </a>
        </div>
        <div class="col-12 p-0 text-center">
            <a href="" class="me-2 small link-secondary link-offset-2 link-underline-opacity-50 link-underline-opacity-100-hover"> {{ __('impressum') }} </a>
            <a href="" class="me-2 small link-secondary link-offset-2 link-underline-opacity-50 link-underline-opacity-100-hover"> {{ __('privacy') }} </a>
        </div>
    </div>

    <div class="text-center mt-3 pb-4">
        <p class="m-0 small" style="color:var(--bl-clr-gray-60);">Tools v{{ env('APP_VERSION', 'VERSION_invalid') }}-{{ Str::upper(env('APP_ENV', 'ENV_invalid')) }}</p>
        <p class="m-0 small" style="color:var(--bl-clr-gray-60);">Systemtime: {{ now()->format('H:i:s d.m.y') }}</p>
        <p class="m-0 small" style="color:var(--bl-clr-gray-70);"><i class="fa-regular fa-copyright fa-xs"></i> BlubberLounge Tools {{ now()->year }}. Some Rights Reserverd.</p>
    </div>
</nav>
