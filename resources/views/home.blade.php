@extends('layouts.app')

@push('scripts')
    <script src="https://accounts.google.com/gsi/client" onload="console.log('TODO: add onload function')"></script>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="text-center">
                        {{ __('You are logged in!') }}
                    </div>
                    <div class="mt-5 text-center">
                        {!! Str::replace('-', '<div>-', Illuminate\Foundation\Inspiring::quotes()->random()) !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{ Laravel\Socialite\Facades\Socialite::driver('google')->redirect(); }}
    {{-- <div class="row">
        <div id="g_id_onload"
            data-client_id="{{ env('GOOGLE_CLIENT_ID') }}"
            data-context="signin"
            data-ux_mode="popup"
            data-login_uri="/auth/callback"
            data-auto_prompt="false">
        </div>

        <div class="g_id_signin"
            data-type="standard"
            data-shape="rectangular"
            data-theme="outline"
            data-text="signup_with"
            data-size="large"
            data-logo_alignment="left">
        </div>
    </div> --}}
</div>
@endsection
