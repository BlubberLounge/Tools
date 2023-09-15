@component('mail::message')
## Guten Tag {{ $invitation->full_name }},

Sie haben soeben eine Zugangsanfrage bei BlubberLounge {{ env('APP_NAME') }} beantragt.
Sobald die Anfrage bearbeitet wurde oder sich der Status ändert werden sie benachrichtigt.<br>

@php($text = 'Zu BlubberLounge ' . env('APP_NAME'))

{{-- Action Button --}}
@component('mail::button', ['url' => env('APP_URL')])
{{ $text }}
@endcomponent

@lang('If you did not request access, no further action is required.')<br><br>

danke,<br>
Ihr BlubberLounge Team

@slot('subcopy')
@lang(
    "If you're having trouble clicking the \":text\" button, copy and paste the URL below\n".
    'into your web browser:',
    [
        'text' => $text,
    ]
) <span class="break-all">[{{ env('APP_URL') }}]({{ env('APP_URL') }})</span>
@endslot

@endcomponent
