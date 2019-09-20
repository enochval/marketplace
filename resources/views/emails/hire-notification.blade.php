@component('mail::message')
# Notification
***

Hi {{ $user->profile->first_name }},

You have been hired for the job you applied for.

@component('mail::button', ['url' => env('FRONTEND_URL')])
    Login to Timbala
@endcomponent

Warm Regards,<br>
{{ config('app.name') }}
@endcomponent
