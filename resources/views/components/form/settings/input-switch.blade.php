<div class="row">
    <div class="col p-0">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="{{ $id }}" @checked(Auth::user()->settings->value($id))>
            <label class="form-check-label" for="{{ $id }}"> {{ __($label) }} </label>
        </div>
        @if($description)
            <p class="form-switch-description text-secondary">
                {{ __($description) }}
            </p>
        @endif
    </div>
</div>
