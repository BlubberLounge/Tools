<div class="mb-3">
    <label for="{{ $attribute }}" class="block mb-1 text-sm font-medium text-base-content"> {{ __($label) }} </label>
    <textarea
        id="{{ $attribute }}"
        class="textarea textarea-bordered w-full resize-none @error('{{ $attribute }}') textarea-error @enderror"
        name="{{ $attribute }}"
        @if($maxRows > 0) rows="{{ $maxRows }}" @endif
        @if($autofocus) autofocus @endif
        autocomplete="off"
    >{{ old($attribute) ? old($attribute) : Auth::user()->$attribute }}</textarea>
    @error('{{ $attribute }}')
        <span class="text-error text-sm" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
