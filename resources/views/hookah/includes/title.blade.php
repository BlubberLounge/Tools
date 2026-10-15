@if(Request::path() !== 'hookah')
    <div class="flex">
        <a href="{{ url()->previous() }}">
            <i class="fa-solid fa-caret-left"></i>
            {{ __('Back') }}
        </a>
    </div>
@endif

<div class="flex flex-wrap pb-4 pt-2">
    <h3 class="w-2/3">
        Hookah Management
        <small class="text-base-content/50">{{ substr(Route::currentRouteAction(), strpos(Route::currentRouteAction(), "@") + 1) }}</small>
    </h3>

    @if(Request::path() === 'hookah')
        <div class="w-1/3 flex justify-end">
            <a class="btn btn-primary" href="{{ route('hookah.create') }}" role="button">
                <i class="fa-solid fa-circle-plus fa-lg pr-2"></i>
                Add Hookah
            </a>
        </div>
    @endif
</div>
