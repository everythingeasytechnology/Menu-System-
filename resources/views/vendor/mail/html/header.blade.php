@props(['url'])
@php
    $mailBrand = app(\App\Services\MailBrandingService::class);
    $brandName = $mailBrand->name();
    $logoUrl = $mailBrand->logoUrl();
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($logoUrl)
<img src="{{ $logoUrl }}" class="logo" alt="{{ $brandName }} Logo">
@else
{{ $brandName }}
@endif
</a>
</td>
</tr>
