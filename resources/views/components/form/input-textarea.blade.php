<div class="mb-3">
    <label for="{{ $attribute }}" class="form-label"> {{ $label }} </label>
    <textarea
        id="{{ $attribute }}"
        class="form-control @error('{{ $attribute }}') is-invalid @enderror"
        name="{{ $attribute }}"
        style="resize: none"
        @if($maxRows > 0) rows="{{ $maxRows }}" @endif
        @if($autofocus) autofocus @endif
        autocomplete="off"
    >{{ old($attribute) ? old($attribute) : Auth::user()->$attribute }}</textarea>
    @error('{{ $attribute }}')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
