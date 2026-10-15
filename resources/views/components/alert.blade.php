<div x-data="{ show: true }" x-show="show" x-transition
    @class([
        'alert text-sm',
        'alert-success' => $type === 'success',
        'alert-error' => $type === 'danger',
        'alert-warning' => $type === 'warning',
        'alert-info' => $type === 'info',
    ])
    role="alert">
    {{ $message }}
    <button type="button" @click="show = false" class="btn btn-ghost btn-xs btn-circle" aria-label="Close">&#x2715;</button>
</div>
