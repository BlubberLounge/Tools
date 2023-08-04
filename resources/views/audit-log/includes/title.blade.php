@if(Request::path() !== 'audit-log')
    <div class="row">
        <a href="{{ url()->previous() }}">
            <i class="fa-solid fa-caret-left"></i>
            {{ __('Back') }}
        </a> 
    </div>
@endif

<div class="row pb-4 pt-2">
    <h3 class="col-8">
        Audit Log
        <small class="text-muted">{{ substr(Route::currentRouteAction(), strpos(Route::currentRouteAction(), "@") + 1) }}</small>
    </h3>
</div>
