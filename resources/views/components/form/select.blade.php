<div class="mb-3">
    <label for="{{ $attribute }}" class="block mb-1 text-sm font-medium text-base-content"> {{ __($label) }} </label>
    <select
        id="{{ $attribute }}"
        class="select select-bordered w-full @error('{{ $attribute }}') select-error @enderror"
        name="{{ $attribute }}"
    >
        @foreach ($options as $key => $option)
            <option value="{{ $key }}" {{ old($attribute) ? 'selected' : ''}}>{{ __($option) }}</option>
        @endforeach
    </select>
    @error('{{ $attribute }}')
        <span class="text-error text-sm" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
