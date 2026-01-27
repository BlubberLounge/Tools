<div class="mb-4">
    <label class="flex items-center cursor-pointer">
        <div class="relative">
            <input type="checkbox" id="{{ $id }}" class="sr-only peer" @checked(Auth::user()->settings->value($id))>
            <div class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:bg-primary transition-colors"></div>
            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
        </div>
        <span class="ml-3 text-sm font-medium text-[var(--tw-body-color)]">{{ __($label) }}</span>
    </label>
    @if($description)
        <p class="mt-1 ml-14 text-sm text-[var(--tw-muted-color)]">
            {{ __($description) }}
        </p>
    @endif
</div>
