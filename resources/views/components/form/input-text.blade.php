<div class="flex flex-wrap items-center gap-2 mb-{{ $bottomSpacing }}">
    <label for="{{ $attribute }}" class="shrink-0 text-sm font-medium text-base-content">{{ __($label) }}</label>
    <input
        id="{{ $attribute }}"
        class="flex-1 input input-bordered @error('{{ $attribute }}') input-error @enderror"
        type="text"
        name="{{ $attribute }}"
        value="{{ old($attribute) ? old($attribute) : '' }}"
        autocomplete="off"
        @if(isset($autofocus)) autofocus @endif
    >
    @error('{{ $attribute }}')
        <span class="text-error text-sm w-full" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
