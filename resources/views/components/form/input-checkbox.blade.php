<div class="row mb-2">
    <div class="col">
        <label for="{{ $attribute }}" @class(["col-form-label d-block pb-0", "with-helptext" => $helptext]) style="line-height: 1">
            {{ __($label) }}
        </label>
        @if($helptext)
            <span style="font-size: .8em;color: gray"> {{ $helptext }} </span>
        @endif
    </div>
    <div class="col-auto form-switch">
        <input
            id="{{ $attribute }}"
            class="form-check-input @error('{{ $attribute }}') is-invalid @enderror"
            type="checkbox"
            name="{{ $attribute }}"
            value="{{ old($attribute) ? old($attribute) : '' }}"
            style="font-size:1.5em"
            {{ $isChecked ? 'checked' : '' }}
            autocomplete="off"
        >
    </div>
</div>
