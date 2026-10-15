<div class="flex flex-wrap items-center gap-2 mb-{{ $bottomSpacing }}">
    <div class="flex-1">
        <label for="{{ $attribute }}" @class(["block pb-0 text-sm font-medium text-base-content", "with-helptext" => $helptext]) style="line-height: 1">
            {{ __($label) }}
        </label>
        @if($helptext)
            <span class="text-xs text-base-content/50"> {{ __($helptext) }} </span>
        @endif
    </div>
    <div class="shrink-0">
        <input
            type="hidden"
            name="{{ $attribute }}"
            value="0"
        />

        <input
            id="{{ $attribute }}"
            class="toggle toggle-primary toggle-sm @error('{{ $attribute }}') toggle-error @enderror"
            type="checkbox"
            name="{{ $attribute }}"
            {{ $isChecked ? 'checked' : '' }}
            autocomplete="off"
        />
    </div>
</div>
