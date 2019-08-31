@component('mail::message')
# Welcome to Timbala
***

There {{ $user->profile->first_name }},

We are glad to welcome you to the wide range of opportunities Timabala has to offer you...

Warm Regards,<br>
{{ config('app.name') }}
@endcomponent
