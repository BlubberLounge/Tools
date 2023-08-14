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
                <span class="nav-text"> Home <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('dart.index') }}" class="nav-link {{ request()->routeIs('dart.index') ? 'active' : '' }}">
                <i class="fa-solid fa-bullseye"></i>
                <span class="nav-text"> Dart Game <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('dart.index') }}" class="nav-link">
                <i class="fa-solid fa-chart-pie"></i>
                <span class="nav-text"> Dart Dashboard <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link has-submenu" data-bs-toggle="collapse" data-bs-target="#submenuDart">
                <i class="fa-solid fa-wrench"></i>
                <span class="nav-text"> Other Dart Tools <span>
            </a>
            <ul class="collapse submenu" id="submenuDart">
                <li class="submenu-item"><a href="{{ route('dart.showDartboard') }}" class="submenu-item"> Dartboard </a></li>
                <li class="submenu-item"><a href="{{ route('dart.showCheckout') }}" class="submenu-item"> Dart Checkout calculator </a></li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" href="#">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span class="nav-text"> Hookahs <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link disabled">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span class="nav-text"> Tobaccos <span>
            </a>
        </li>
        <li class="nav-item dropdown">
            <a href="#" class="nav-link has-submenu" data-bs-toggle="collapse" data-bs-target="#submenuCalculators">
                <i class="fa-solid fa-calculator"></i>
                <span class="nav-text"> Calculators <span>
            </a>
            <ul class="collapse submenu" id="submenuCalculators">
                <li class="submenu-item">
                    <a href="#" class="submenu-link">
                        Coal Calculator
                    </a>
                </li>
                <li class="submenu-item">
                    <a href="#" class="submenu-link">
                        Tobacco Calculator
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a href="{{ route('battery') }}" class="nav-link {{ request()->routeIs('battery') ? 'active' : '' }}">
                <i class="fa-solid fa-battery-three-quarters fa-rotate-270"></i>
                <span class="nav-text"> Battery Simulation <span>
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
                <span class="nav-text"> Settings <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('device.index') }}" class="nav-link">
                <i class="fa-solid fa-desktop"></i>
                <span class="nav-text"> Device <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('user.index') }}" class="nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}" >
                <i class="fa-solid fa-globe"></i>
                <span class="nav-text"> Language <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('feedback.create') }}" class="nav-link {{ request()->routeIs('feedback.create') ? 'active' : '' }}" >
                <i class="fa-solid fa-circle-question"></i>
                <span class="nav-text"> Feedback <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('faq.index') }}" class="nav-link {{ request()->routeIs('faq.*') ? 'active' : '' }}" >
                <i class="fa-solid fa-comment-dots"></i>
                <span class="nav-text"> Tools FAQ <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('audit-log.index') }}" class="nav-link {{ request()->routeIs('audit-log.*') ? 'active' : '' }}">
                <i class="fa-solid fa-list-ul"></i>
                <span class="nav-text"> Audit Log <span>
            </a>
        </li>
        <li class="nav-item mt-2">
            <a href="{{ route('user.index') }}" class="nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}" >
                <i class="fa-solid fa-users"></i>
                <span class="nav-text"> User Management <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('feedback.index') }}" class="nav-link {{ request()->routeIs('feedback.index') ? 'active' : '' }}" >
                <i class="fa-solid fa-circle-question"></i>
                <span class="nav-text"> User Feedback <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('l5-swagger.default.api') }}" class="nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}" >
                <i class="fa-solid fa-users"></i>
                <span class="nav-text"> Api documentation <span>
            </a>
        </li>
        <li class="nav-item mt-3">
            <a href="{{ route('logout') }}" class="nav-link text-danger" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span class="nav-text"> {{ __('Logout') }} <span>
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
            Light
            <i class="bi ms-auto d-none fa-solid fa-check"></i>
        </button>
        <button type="button" class="btn btn-dark d-flex align-items-center" data-bs-theme-value="dark">
            <i class="bi me-2 fa-solid fa-moon opacity-50 theme-icon" data-bs-theme-icon="fa-moon"></i>
            Dark
            <i class="bi ms-auto d-none fa-solid fa-check"></i>
        </button>
        <button type="button" class="btn btn-dark d-flex align-items-center active" data-bs-theme-value="auto">
            <i class="bi me-2 fa-solid fa-circle-half-stroke opacity-50 theme-icon" data-bs-theme-icon="fa-circle-half-stroke"></i>
            Auto
            <i class="bi ms-auto d-none fa-solid fa-check"></i>
        </button>
    </div>
    <div class="btn-group btn-group-sm" id="bd-theme">
        <button type="button" class="btn btn-dark d-flex align-items-center" data-bs-theme-value="light">
            <i class="bi me-2 fa-solid fa-sun opacity-50 theme-icon" data-bs-theme-icon="fa-sun"></i>
            Light
            <i class="bi ms-auto d-none fa-solid fa-check"></i>
        </button>
        <button type="button" class="btn btn-dark d-flex align-items-center" data-bs-theme-value="dark">
            <i class="bi me-2 fa-solid fa-moon opacity-50 theme-icon" data-bs-theme-icon="fa-moon"></i>
            Dark
            <i class="bi ms-auto d-none fa-solid fa-check"></i>
        </button>
        <button type="button" class="btn btn-dark d-flex align-items-center active" data-bs-theme-value="auto">
            <i class="bi me-2 fa-solid fa-circle-half-stroke opacity-50 theme-icon" data-bs-theme-icon="fa-circle-half-stroke"></i>
            Auto
            <i class="bi ms-auto d-none fa-solid fa-check"></i>
        </button>
    </div>
    <ul class="list-group">
        {{-- @foreach ($availableLanguages as $k => $locale)
            <li class="list-group-item">
                <input class="form-check-input me-1" type="radio" name="locale" value="{{ $locale }}" id="locale_{{ $locale }}" @checked($locale == App::currentLocale())>
                <label class="form-check-label" for="locale_{{ $locale }}">
                    {{ __($k) }} <span class="fi fi-{{ $locale === 'en' ? 'gb' : $locale}}"></span>
                </label>
            </li>
        @endforeach --}}
    </ul>
</nav>
