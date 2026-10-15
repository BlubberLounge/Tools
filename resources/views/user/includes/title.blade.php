<div class="flex justify-between items-center pt-2">
    <h3>
        User Management
        <small class="text-base-content/50">{{ substr(Route::currentRouteAction(), strpos(Route::currentRouteAction(), "@") + 1) }}</small>
    </h3>

    @if(Request::path() === 'user')
        <div>
            <a class="btn btn-primary" href="{{ route('user.create') }}" role="button">
                <i class="fa-solid fa-circle-plus fa-lg pr-2"></i>
                Add User
            </a>
        </div>
    @endif
</div>
