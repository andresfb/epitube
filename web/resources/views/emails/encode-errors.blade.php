@component('mail::message')
# Errors Encoding Media Found

The are {{ $count }} encoding errors found since the last import.

@component('mail::button', ['url' => $url])
Click to see the list
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
