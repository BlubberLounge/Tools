<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
    <img src="https://media.maximilian-mewes.de/project/bl/blubber_lounge_rebrand_try_white_optimized.svg">
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{{ $subcopy }}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
<x-slot:links>
[{{ config('app.name') }}]({{config('app.url')}}) | [GitHub](https://github.com/BlubberLounge) | [BlubberLounge](https://blubber-lounge.de/)
</x-slot:links>
<x-slot:copyright>
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
</x-slot:copyright>
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
