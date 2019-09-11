@component('mail::message')
# Welcome to Timbala
***

Dear {{ $user->profile->first_name }},

Your job post has been sent successfully. 
Our admin will review and push to dashboard within 24hours.

Warm Regards,<br>
{{ config('app.name') }}
@endcomponent
