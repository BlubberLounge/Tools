<div x-data="{ show: true }" x-show="show" x-transition class="alert alert-{{ $type }} alert-dismissible" role="alert">
    {{ $message }}
    <button type="button" @click="show = false" class="btn-close" aria-label="Close"></button>
</div>
