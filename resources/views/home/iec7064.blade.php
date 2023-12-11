@extends('layouts.app')

@push('scripts')
    <script src="{{ mix('js/IEC7064_page.js') }}" defer></script>
@endpush

@section('content')
<div class="container">
    <div class="row justify-center">
        <div class="col-5">
            <div class="mb-3">
                <label for="inputString" class="form-label">Input</label>
                <input type="text" class="form-control" id="inputString" placeholder="Enter string here">
            </div>
            <div class="mb-3">
                <label for="outputChecksum" class="form-label">Checksum</label>
                <input type="text" class="form-control" id="outputChecksum" placeholder="checksum" readonly>
            </div>
            <div class="mb-3">
                <label for="outputWholeString" class="form-label">Input + Checksum</label>
                <input type="text" class="form-control" id="outputWholeString" placeholder="input + checksum" readonly>
            </div>
            <div class="mb-3">
                <p id="isValid">
                    unkown
                </p>
            </div>
        </div>
        <div class="col-5">
            <button id="btnValidate" class="btn btn-primary"> validate </button>
            <button id="btnGenerate" class="btn btn-primary"> Generate </button>
        </div>
    </div>
</div>
@endsection
