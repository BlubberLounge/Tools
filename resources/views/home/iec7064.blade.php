@extends('layouts.app')

@push('scripts')
    <script src="{{ mix('js/IEC7064_page.js') }}" defer></script>
@endpush

@section('content')
<div class="container">
    <div class="row">
        <div class="col">
            <input class="input" id="inputString" placeholder="Enter string here">
        </div>
        <div class="col">
            <button id="btnValidate" class="btn btn-primary"> validate </button>
            <button id="btnGenerate" class="btn btn-primary"> Generate </button>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <input type="text" class="input" id="outputChecksum" disabled>
        </div>
        <div class="col">
            <input type="text" class="input" id="outputWholeString" disabled>
        </div>
        <div class="col">
            <p id="isValid">unkown</p>
        </div>
    </div>
</div>
@endsection
