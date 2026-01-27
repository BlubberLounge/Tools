<div class="flex flex-wrap items-center gap-2 mb-{{ $bottomSpacing }}">
    <label for="{{ $attribute }}" class="shrink-0 text-sm font-medium text-[var(--tw-body-color)]">{{ __($label) }}</label>
    <input
        id="{{ $attribute }}"
        class="flex-1 form-control @error('{{ $attribute }}') is-invalid @enderror"
        type="text"
        name="{{ $attribute }}"
        value="{{ old($attribute) ? old($attribute) : '' }}"
        autocomplete="off"
        @if(isset($autofocus)) autofocus @endif
    >
    @error('{{ $attribute }}')
        <span class="invalid-feedback w-full" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
