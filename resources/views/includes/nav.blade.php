<nav class="navbar navbar-expand-md navbar-light shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="http://media.maximilian-mewes.de/project/bl/blubber_lounge_rebrand_try.svg" alt="Dart a Web-App Logo" width="100px" style="transform:rotate(-2deg);">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            @auth
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav me-auto">
                <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{-- request()->routeIs('utillity.viewCheckouts') || request()->routeIs('utillity.viewDartboard')? 'active' : '' --}}" id="navbarDropdown" href="#" data-bs-toggle="dropdown"> Calculators </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="{{-- route('utillity.viewCheckouts') --}}"> Coal Calculator </a></li>
                            <li><a class="dropdown-item" href="#"> Tobacco Calculator</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <!-- <li><a class="dropdown-item" href="{{-- route('utillity.viewDartboard') --}}"> behind the scenes </a></li> -->
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#"> Hookahs </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#"> Tabaccos </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#"> History </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/battery') }}"> Battery </a>
                    </li>
                </ul>
            @endauth

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ms-auto">
                <!-- Authentication Links -->
                @guest
                    @if (Route::has('login'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                    @endif

                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->name }}
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            
                            <li><a class="dropdown-item" href="#"> Settings </a></li>
                            @can('viewAny', App\Models\User::class)
                                <li>
                                    <a class="dropdown-item" href="#">
                                        Audit Log
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="dropdown-item {{ request()->routeIs('user.*') ? 'active' : '' }}" href="{{ route('user.index') }}"> User Management </a>
                                </li>
                            @endcan
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
                    </li>
                    <li class="nav-item dropdown">
                        <button class="btn btn-link nav-link py-2 px-0 px-lg-2 dropdown-toggle d-flex align-items-center" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" data-bs-display="static">
                          <i class="bi my-1 fa-solid fa-moon theme-icon-active" data-bs-theme-icon="fa-moon"></i>
                          <span class="d-lg-none ms-2">Toggle theme</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bd-theme" style="--bs-dropdown-min-width: 8rem;">
                          <li>
                            <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light">
                              <i class="bi me-2 fa-solid fa-sun opacity-50 theme-icon" data-bs-theme-icon="fa-sun"></i>
                              Light
                              <i class="bi ms-auto d-none fa-solid fa-check"></i>
                            </button>
                          </li>
                          <li>
                            <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="dark">
                                <i class="bi me-2 fa-solid fa-moon opacity-50 theme-icon" data-bs-theme-icon="fa-moon"></i>
                              Dark
                              <i class="bi ms-auto d-none fa-solid fa-check"></i>
                            </button>
                          </li>
                          <li>
                            <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="auto">
                              <i class="bi me-2 fa-solid fa-circle-half-stroke opacity-50 theme-icon" data-bs-theme-icon="fa-circle-half-stroke"></i>
                              Auto
                              <i class="bi ms-auto d-none fa-solid fa-check"></i>
                            </button>
                          </li>
                        </ul>
                      </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>