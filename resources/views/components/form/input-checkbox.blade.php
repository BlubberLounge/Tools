<div class="flex flex-wrap items-center gap-2 mb-{{ $bottomSpacing }}">
    <div class="flex-1">
        <label for="{{ $attribute }}" @class(["block pb-0 text-sm font-medium text-[var(--tw-body-color)]", "with-helptext" => $helptext]) style="line-height: 1">
            {{ __($label) }}
        </label>
        @if($helptext)
            <span class="text-xs text-[var(--tw-muted-color)]"> {{ __($helptext) }} </span>
        @endif
    </div>
    <div class="shrink-0 form-switch">
        <input
            type="hidden"
            name="{{ $attribute }}"
            value="0"
        />

        <input
            id="{{ $attribute }}"
            class="form-check-input w-12 h-6 @error('{{ $attribute }}') is-invalid @enderror"
            type="checkbox"
            name="{{ $attribute }}"
            {{ $isChecked ? 'checked' : '' }}
            autocomplete="off"
        />
    </div>
</div>
