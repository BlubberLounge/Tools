<div class="mb-3">
    <label for="{{ $attribute }}" class="form-label"> {{ $label }} </label>
    <select
        id="{{ $attribute }}"
        class="form-select @error('{{ $attribute }}') is-invalid @enderror"
        name="{{ $attribute }}"
    >
        @foreach ($options as $key => $option)
            <option value="{{ $key }}" {{ old($attribute) ? 'selected' : ''}}>{{ $option }}</option>
        @endforeach
    </select>
    @error('{{ $attribute }}')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
