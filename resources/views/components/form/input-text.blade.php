<div class="row mb-{{ $bottomSpacing }}">
    <label for="{{ $attribute }}" class="col-auto text-md-start col-form-label">{{ __($label) }}</label>
    <input
        id="{{ $attribute }}"
        class="col form-control @error('{{ $attribute }}') is-invalid @enderror"
        type="text"
        name="{{ $attribute }}"
        value="{{ old($attribute) ? old($attribute) : '' }}"
        autocomplete="off"
        @if(isset($autofocus)) $autofocus @endif
    >
    @error('{{ $attribute }}')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>