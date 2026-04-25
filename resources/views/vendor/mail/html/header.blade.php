@props(['url'])
@php
    $logoUrl = \App\Models\SiteSetting::logoUrl();
    $siteName = \App\Models\SiteSetting::siteName();
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($logoUrl)
<img src="{{ $logoUrl }}" class="logo" alt="{{ $siteName }}" style="max-height: 75px; max-width: 220px; height: auto; width: auto;">
@elseif (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo-v2.1.png" class="logo" alt="Laravel Logo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
