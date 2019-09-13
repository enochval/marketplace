@component('mail::message')
# Payment Notification
***

Hi {{ $user->profile->first_name }},

The payment of {{ $amount }} was {{ $status }}.

Warm Regards,<br>
{{ config('app.name') }}
@endcomponent
