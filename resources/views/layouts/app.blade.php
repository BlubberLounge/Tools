@mobile
    @include('layouts.app_mobile')
@endmobile

{{-- may block requests from desktop soon --}}
@desktop
    @include('layouts.app_mobile')
@enddesktop

{{--
@browser('isBot')
    <p>Bots can be identified too :)</p>
@endbrowser
--}}
