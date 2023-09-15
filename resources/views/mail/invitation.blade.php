@php($url = route('register.register', $invitation->token))

@component('mail::message')
## Guten Tag {{ $invitation->full_name }},

Ihre Zugangsanfrage wurde soeben <span style="color:{{ $invitation->status === \App\Enums\InvitationStatus::APPROVED ? '#67ab24' : '#bc1a1a' }}">{{ __($invitation->status->value) }}.</span><br>
@if($invitation->isApproved())
Sie haben dadurch den Zugriff auf das Registrierungsformular erhalten.<br>
Klicke auf folgenden Link<br>

{{-- Action Button --}}
@component('mail::button', ['url' => $url])
Zum Registrierungsformular
@endcomponent
@endif

@if($invitation->isApproved())
@lang('If you did not request access, no further action is required.')<br><br>
@endif

danke,<br>
Ihr BlubberLounge Team


@if($invitation->isApproved())
@slot('subcopy')
@lang(
    "If you're having trouble clicking the \":text\" button, copy and paste the URL below\n".
    'into your web browser:',
    [
        'text' => 'Zum Registrierungsformular',
    ]
) <span class="break-all">[{{ $url }}]({{ $url }})</span>
@endslot
@else
@slot('subcopy')
@lang('If you did not request access, no further action is required.')
@endslot
@endif

@endcomponent
