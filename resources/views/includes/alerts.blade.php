@if(session()->has('info'))
    <div x-data="{ show: true }" x-show="show" x-transition class="alert flex items-center justify-between bg-primary/10 border border-primary/20 text-primary text-info" role="alert">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-circle-info fa-xl"></i>
            <span>{{ session()->get('info') }}</span>
        </div>
        <button type="button" @click="show = false" class="btn btn-ghost btn-xs btn-circle shrink-0" aria-label="Close">✕</button>
    </div>
@endif

@if(session()->has('success'))
    <div x-data="{ show: true }" x-show="show" x-transition class="alert alert-success flex items-center justify-between" role="alert">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-circle-check fa-xl"></i>
            <span>{{ session()->get('success') }}</span>
        </div>
        <button type="button" @click="show = false" class="btn btn-ghost btn-xs btn-circle shrink-0" aria-label="Close">✕</button>
    </div>
@endif

@if(session()->has('warning'))
    <div x-data="{ show: true }" x-show="show" x-transition class="alert flex items-center justify-between bg-warning/10 border border-warning/20 text-warning text-warning" role="alert">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation fa-xl"></i>
            <span>{{ session()->get('warning') }}</span>
        </div>
        <button type="button" @click="show = false" class="btn btn-ghost btn-xs btn-circle shrink-0" aria-label="Close">✕</button>
    </div>
@endif

@if(session()->has('error'))
    <div x-data="{ show: true }" x-show="show" x-transition class="alert alert-error flex items-center justify-between" role="alert">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-bug fa-xl"></i>
            <span>{{ session()->get('error') }}</span>
        </div>
        <button type="button" @click="show = false" class="btn btn-ghost btn-xs btn-circle shrink-0" aria-label="Close">✕</button>
    </div>
@endif
