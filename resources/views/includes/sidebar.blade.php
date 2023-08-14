<nav id="sidebar" class="p-3 bg-body-tertiary active">
    <div class="d-flex justify-center py-3">
        <a class="d-flex align-items-center" href="{{ url('/') }}">
            <img src="https://media.maximilian-mewes.de/project/bl/blubber_lounge_rebrand_try_white_optimized.svg" class="nav-brand" alt="BlubberLounge Logo" width="100px">
        </a>
        <div class="vertical-divider"></div>
        <a class="nav-brand-sub d-flex align-items-center" href="{{ url('/') }}">
            <i class="fa-solid fa-screwdriver-wrench"></i>
        </a>
    </div>

    <hr class="mb-2" />

    <ul class="nav nav-pills flex-column mb-5">{{-- mb-auto --}}
        <li class="nav-item">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home.*') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i>
                <span class="nav-text"> {{ __('home') }} <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('dart.game.index') }}" class="nav-link {{ request()->routeIs('dart.game.index') ? 'active' : '' }}">
                <i class="fa-solid fa-bullseye"></i>
                <span class="nav-text"> {{ __('dart game') }} <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('dart.index') }}" class="nav-link">
                <i class="fa-solid fa-chart-pie"></i>
                <span class="nav-text"> {{ __('dart dashboard') }} <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('dart.show-info') }}" class="nav-link {{ request()->routeIs('dart.show-info') ? 'active' : '' }}">
                <i class="fa-solid fa-circle-info"></i>
                <span class="nav-text"> {{ __('dart information') }} <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('dart.show-checkout') }}" class="nav-link {{ request()->routeIs('dart.show-checkout') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i>
                <span class="nav-text"> {{ __('dart checkout calculator') }} <span>
            </a>
        </li>
        {{-- <li class="nav-item">
            <a href="#" class="nav-link has-submenu" data-bs-toggle="collapse" data-bs-target="#submenuDart">
                <i class="fa-solid fa-wrench"></i>
                <span class="nav-text"> {{ __('other dart tools') }} <span>
            </a>
            <ul class="collapse submenu" id="submenuDart">
                <li class="submenu-item">
                    <a href="{{ route('dart.showDartboard') }}" class="submenu-item">
                        {{ __('dartboard') }}
                    </a>
                </li>
                <li class="submenu-item">
                    <a href="{{ route('dart.showCheckout') }}" class="submenu-item">
                        {{ __('dart checkout calculator') }}
                    </a>
                </li>
            </ul>
        </li> --}}
        <li class="nav-item">
            <a class="nav-link disabled" href="#">
                <i class="fa-solid fa-bong"></i>
                <span class="nav-text"> {{ __('Hookahs') }} <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link disabled">
                <i class="fa-solid fa-box-open"></i>
                <span class="nav-text"> {{ __('Tobaccos') }} <span>
            </a>
        </li>
        <li class="nav-item dropdown">
            <a href="#" class="nav-link has-submenu" data-bs-toggle="collapse" data-bs-target="#submenuCalculators">
                <i class="fa-solid fa-calculator"></i>
                <span class="nav-text"> {{ __('calculators') }} <span>
            </a>
            <ul class="collapse submenu" id="submenuCalculators">
                <li class="submenu-item">
                    <a href="#" class="submenu-link">
                        {{ __('coal calculator') }}
                    </a>
                </li>
                <li class="submenu-item">
                    <a href="#" class="submenu-link">
                        {{ __('tobacco calculator') }}
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a href="{{ route('battery') }}" class="nav-link {{ request()->routeIs('battery') ? 'active' : '' }}">
                <i class="fa-solid fa-battery-three-quarters fa-rotate-270"></i>
                <span class="nav-text"> {{ __('battery simulation') }} <span>
            </a>
        </li>
    </ul>

    <hr class="mt-5" />

    <ul class="nav nav-pills nav-sm flex-column mb-5">
        <li class="nav-item">
            <a href="#" style="display: flex;align-items: center;padding: var(--bs-nav-link-padding-y) var(--bs-nav-link-padding-x);">
                <img src="{{ Auth::user()->img }}" width="48" class="rounded-circle me-2">
                <strong> {{ Auth::user()->full_name }} </strong>
            </a>
        </li>
        <li class="nav-item mt-2">
            <a class="nav-link" href="#">
                <i class="fa-solid fa-gears"></i>
                <span class="nav-text"> {{ __('settings') }} <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('device.index') }}" class="nav-link">
                <i class="fa-solid fa-desktop"></i>
                <span class="nav-text"> {{ __('devices') }} <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('feedback.create') }}" class="nav-link {{ request()->routeIs('feedback.create') ? 'active' : '' }}" >
                <i class="fa-solid fa-circle-question"></i>
                <span class="nav-text"> {{ __('feedback') }} <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('faq.index') }}" class="nav-link {{ request()->routeIs('faq.*') ? 'active' : '' }}" >
                <i class="fa-solid fa-comment-dots"></i>
                <span class="nav-text"> {{ __('tools FAQ') }} <span>
            </a>
        </li>
        <li class="nav-item mt-2">
            <a href="{{ route('audit-log.index') }}" class="nav-link {{ request()->routeIs('audit-log.*') ? 'active' : '' }}">
                <i class="fa-solid fa-list-ul"></i>
                <span class="nav-text"> {{ __('audit log') }} <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('user.index') }}" class="nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}" >
                <i class="fa-solid fa-users"></i>
                <span class="nav-text"> {{ __('user management') }} <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('feedback.index') }}" class="nav-link {{ request()->routeIs('feedback.index') ? 'active' : '' }}" >
                <i class="fa-solid fa-circle-question"></i>
                <span class="nav-text"> {{ __('user feedback') }} <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('l5-swagger.default.api') }}" class="nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}" >
                <i class="fa-solid fa-users"></i>
                <span class="nav-text"> {{ __('api documentation') }} <span>
            </a>
        </li>
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
</nav>
