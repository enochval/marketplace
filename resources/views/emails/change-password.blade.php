@component('mail::message')
# Password Changed
***

Hi {{ $user->profile->first_name }},

This is to notify you that your password was successfully updated.

PS: Contact the admin if you didn't perform this operation

@component('mail::button', ['url' => env('FRONTEND_URL')])
    Login to Timbala
@endcomponent

Warm Regards,<br>
{{ config('app.name') }}
@endcomponent
