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

    <ul class="nav nav-pills flex-column">{{-- mb-auto --}}
        <li class="nav-item">
            <a href="{{ route('dart.index') }}" class="nav-link {{ request()->routeIs('dart.index') ? 'active' : '' }}">
                <i class="fa-solid fa-bullseye"></i>
                <span class="nav-text"> Dart Game <span>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="fa-solid fa-bars-progress"></i>
                <span class="nav-text"> Dart Management <span>
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

    <div class="mt-5">
        <a href="#" class="has-submenu" data-bs-toggle="collapse" data-bs-target="#submenuProfile">
            <img src="{{ Auth::user()->img }}" width="32" class="rounded-circle me-2">
            <strong> {{ Auth::user()->full_name }} </strong>
        </a>
        <ul class="collapse submenu" id="submenuProfile">
            <li class="submenu-item">
                <a class="submenu-link" href="#">
                    Settings
                </a>
            </li>
            <li class="submenu-item">
                <a href="{{ route('device.index') }}" class="submenu-link">
                    Device
                </a>
            </li>
            <li class="submenu-item">
                <a href="{{ route('auditLog') }}" class="submenu-link">
                    Audit Log
                </a>
            </li>
            <li class="submenu-item">
                <a href="{{ route('user.index') }}" class="submenu-item {{ request()->routeIs('user.*') ? 'active' : '' }}" >
                    User Management
                </a>
            </li>
            <li class="submenu-item">
                <a class="submenu-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}
                </a>
            </li>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </ul>
    </div>

    <hr />

    <div class="btn-group" id="bd-theme">
        <button type="button" class="btn d-flex border-0 align-items-center" data-bs-theme-value="light">
            <i class="bi me-2 fa-solid fa-sun opacity-50 theme-icon" data-bs-theme-icon="fa-sun"></i>
            Light
            <i class="bi ms-auto d-none fa-solid fa-check"></i>
        </button>
        <button type="button" class="btn d-flex border-0 align-items-center" data-bs-theme-value="dark">
            <i class="bi me-2 fa-solid fa-moon opacity-50 theme-icon" data-bs-theme-icon="fa-moon"></i>
            Dark
            <i class="bi ms-auto d-none fa-solid fa-check"></i>
        </button>
        <button type="button" class="btn d-flex border-0 align-items-center active" data-bs-theme-value="auto">
            <i class="bi me-2 fa-solid fa-circle-half-stroke opacity-50 theme-icon" data-bs-theme-icon="fa-circle-half-stroke"></i>
            Auto
            <i class="bi ms-auto d-none fa-solid fa-check"></i>
        </button>
    </div>
</nav>
