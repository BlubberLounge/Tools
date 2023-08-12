<nav id="sidebar" class="d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary">
    <a class="d-flex justify-center " href="{{ url('/') }}">
        <img src="https://media.maximilian-mewes.de/project/bl/blubber_lounge_rebrand_try_white_optimized.svg" id="navBrand" alt="BlubberLounge Logo" width="100px">
    </a>

    <hr />

    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dart.index') ? 'active' : '' }}" href="{{ route('dart.index') }}">
                <i class="fa-solid fa-puzzle-piece"></i>
                Dart Game
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">
                <i class="fa-solid fa-bars-progress"></i>
                Dart Management
            </a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" data-bs-toggle="dropdown"> Other Dart Tools </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="{{ route('dart.showDartboard') }}"> Dartboard </a></li>
                <li><a class="dropdown-item" href="{{ route('dart.showCheckout') }}"> Dart Checkout calculator </a></li>
                <li><hr class="dropdown-divider"></li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" href="#"> Hookahs </a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" href="#"> Tabaccos </a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{-- request()->routeIs('utillity.viewCheckouts') || request()->routeIs('utillity.viewDartboard')? 'active' : '' --}}" id="navbarDropdown" href="#" data-bs-toggle="dropdown">
                Calculators
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="{{-- route('utillity.viewCheckouts') --}}"> Coal Calculator </a></li>
                <li><a class="dropdown-item" href="#"> Tobacco Calculator</a></li>
                <li><hr class="dropdown-divider"></li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('battery') ? 'active' : '' }}" href="{{ route('battery') }}">
                <i class="fa-solid fa-battery-three-quarters fa-rotate-270"></i>
                Battery
            </a>
        </li>
    </ul>

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

    <hr />

    <div class="dropdown">
        <a href="#" class="d-flex align-items-center link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">
            <strong>mdo</strong>
        </a>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item" href="#">
                    Settings
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('device.index') }}">
                    Device
                </a>
            </li>
                <li>
                    <a class="dropdown-item" href="{{ route('auditLog') }}">
                        Audit Log
                    </a>
                </li>
                <li class="nav-item">
                    <a class="dropdown-item {{ request()->routeIs('user.*') ? 'active' : '' }}" href="{{ route('user.index') }}">
                        User Management
                    </a>
                </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}
                </a>
            </li>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </ul>
    </div>
</nav>
