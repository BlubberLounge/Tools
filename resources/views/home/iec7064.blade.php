@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/IEC7064_page.js'])
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex flex-wrap justify-center gap-6">
        <div class="w-full md:w-5/12">
            <div class="card card-bordered bg-base-100 p-4">
                <div class="flex items-center gap-2 pb-3 mb-3 border-b border-base-300 text-primary">
                    <i class="fa-solid fa-barcode text-lg"></i>
                    <h5>IEC 7064 Checksum</h5>
                </div>
                <div class="mb-3">
                    <label for="inputString" class="label">Input</label>
                    <input type="text" class="input input-bordered w-full" id="inputString" placeholder="Enter string here">
                </div>
                <div class="mb-3">
                    <label for="outputChecksum" class="label">Checksum</label>
                    <input type="text" class="input input-bordered w-full" id="outputChecksum" placeholder="checksum" readonly>
                </div>
                <div class="mb-3">
                    <label for="outputWholeString" class="label">Input + Checksum</label>
                    <input type="text" class="input input-bordered w-full" id="outputWholeString" placeholder="input + checksum" readonly>
                </div>
                <div class="mb-3">
                    <p id="isValid" class="text-base-content/50">
                        unkown
                    </p>
                </div>
            </div>
        </div>
        <div class="w-full md:w-5/12 flex items-start gap-3">
            <button id="btnValidate" class="btn btn-primary"> validate </button>
            <button id="btnGenerate" class="btn btn-primary"> Generate </button>
        </div>
    </div>
</div>
@endsection
