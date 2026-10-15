@if(Request::path() !== 'audit-log')
    <div>
        <a href="{{ url()->previous() }}">
            <i class="fa-solid fa-caret-left"></i>
            {{ __('Back') }}
        </a>
    </div>
@endif

<div class="flex justify-between items-center pb-4 pt-2">
    <h3>
        Audit Log
        <small class="text-base-content/50">{{ substr(Route::currentRouteAction(), strpos(Route::currentRouteAction(), "@") + 1) }}</small>
    </h3>
</div>
