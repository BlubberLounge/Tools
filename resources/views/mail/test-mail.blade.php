<x-mail::message>
## Guten Tag {{ Auth::user()->full_name }},

This is a test E-mail. it is used to test the design as well as Laravels email features.

Feel free to play around with this 'template'

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thank you,<br>
The BlubberLounge Team
</x-mail::message>
