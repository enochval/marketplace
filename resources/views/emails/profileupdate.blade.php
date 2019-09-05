@component('mail::message')
# Welcome to Timbala
***

Dear {{ $user->profile->first_name }},

Your profile has been successfully updated.

Warm Regards,<br>
{{ config('app.name') }}
@endcomponent
