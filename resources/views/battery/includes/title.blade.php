@if(Request::path() !== 'battery')
    <div class="flex">
        <a href="{{ url()->previous() }}">
            <i class="fa-solid fa-caret-left"></i>
            {{ __('Back') }}
        </a>
    </div>
@endif

<div class="flex flex-wrap pb-2 pt-2">
    <h3 class="w-5/12">
        Battery
        <small class="text-base-content/50">{{ substr(Route::currentRouteAction(), strpos(Route::currentRouteAction(), "@") + 1) == "index" ? "Dashboard" : substr(Route::currentRouteAction(), strpos(Route::currentRouteAction(), "@")) }}</small>
    </h3>
    <div class="flex-1">
        <small>Simulation main loop state:</small> <span id="mainLoopState"> running </span>
    </div>
</div>
