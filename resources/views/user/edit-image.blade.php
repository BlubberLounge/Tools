@extends('layouts.app')

@push('scripts')
    <script src="{{ mix('js/user-profilepicture.js') }}"></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cropper.min.css') }}" />
@endpush

@section('content')
<div class="container-fluid">
    <form action="{{ route('user.update', ['user' => $user->id]) }}" method="POST" enctype="multipart/form-data" id="form-user-image">
        @csrf
        @method('PUT')

        {{-- <div class="row justify-center mt-4 step-1">
            <div class="col-auto">
                <div class="row justify-center mb-4">
                    <div class="col-auto">
                        <img src="{{ asset($user->img) }}" width="200px">
                    </div>
                </div>
                <div class="row justify-center">
                    <div class="col-auto mb-3">
                        <label for="originalImage" class="btn btn-secondary btn-upload btn-lg"> Upload Image </label>
                        <input type="file" name="originalImage" id="originalImage" accept="image/*" class="form-file">
                    </div>
                </div>
            </div>
        </div> --}}
        <div class="row justify-center mt-2 mb-3 p-3 step-2">
            <div class="col-md-9">
                <div style="display:block;max-height: 497px;min-height: 200px;">
                    <img id="image" src="{{ asset($user->img) }}" class="display:none; max-width: 90%;">
                </div>
            </div>
        </div>
        <div class="row justify-center mb-4 step-2">
            <div class="col-auto">
                <div class="img-preview" style="height: 100px; width: 100px;overflow: hidden;">
                    <!-- Image preview -->
                </div>
            </div>
        </div>
        <div class="row justify-content-end">
            <div class="col-auto step-2">
                <button type="button" id="cropImage" class="btn btn-success">Crop</button>
            </div>
            <div class="col-auto step-2">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
            <input type="hidden" name="croppedImage" id="croppedImage">
        </div>
        <div style="height: 25px;"></div>
    </form>
</div>
@endsection
