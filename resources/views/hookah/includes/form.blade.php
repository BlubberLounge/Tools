<div class="flex flex-wrap justify-center">
    <form action="{{ $action === 'create' ? route('hookah.store') : route('hookah.update', $hookah->id) }}" method="POST" id="hookahForm">
        @csrf
        @if($action !== 'create')
            @method('PUT')
        @endif
        <div class="mb-3">
            <label for="name" class="block text-sm font-medium mb-1">{{ __('Name') }}</label>
            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') || $action === 'create' ? old('name') : $hookah->name }}" required autocomplete="off" autofocus>
            @error('name')
                <span class="text-error text-sm mt-1" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="block text-sm font-medium mb-1">{{ __('Description') }}</label>
            <input id="description" type="text" class="form-control @error('description') is-invalid @enderror" name="description" value="{{ old('description') || $action === 'create' ? old('description') : $hookah->description }}" autocomplete="off">
            @error('description')
                <span class="text-error text-sm mt-1" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="manufacturer" class="block text-sm font-medium mb-1">{{ __('Manufacturer') }}</label>
            <select id="manufacturer" name="manufacturer" form="hookahForm" class="form-select" size="5" aria-label="size 5 select example" required>
                @foreach($manufacturers as $manufacturer)
                    <option value="{{ $manufacturer->id }}" {{ isset($hookah) ? ($hookah->role_id === $manufacturer->id) ? 'selected' : '' : '' }}>{{ $manufacturer->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
    </form>
</div>