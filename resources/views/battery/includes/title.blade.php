@if(Request::path() !== 'battery')
    <div class="row">
        <a href="{{ url()->previous() }}">
            <i class="fa-solid fa-caret-left"></i>
            {{ __('Back') }}
        </a> 
    </div>
@endif

<div class="row pb-2 pt-2">
    <h3 class="col-5">
        Battery
        <small class="text-muted">{{ substr(Route::currentRouteAction(), strpos(Route::currentRouteAction(), "@") + 1) == "index" ? "Dashboard" : substr(Route::currentRouteAction(), strpos(Route::currentRouteAction(), "@")) }}</small>
    </h3>
    <div class="col">
        <small>Simulation main loop state:</small> <span id="mainLoopState"> running </span>
    </div>
</div>
