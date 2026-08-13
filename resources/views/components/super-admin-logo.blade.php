@props([
    'imageBoxClass' => 'inline-flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/10 bg-white p-1 shadow-lg',
    'fallbackBoxClass' => 'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange text-white shadow-lg shadow-orange/30',
    'imageClass' => 'h-full w-full object-contain',
    'iconClass' => 'h-6 w-6',
    'alt' => null,
])

@php
    $brand = app(\App\Services\MailBrandingService::class);
    $logoUrl = $brand->logoUrl();
    $brandName = $brand->name();
@endphp

@if($logoUrl)
    <span {{ $attributes->merge(['class' => $imageBoxClass]) }}>
        <img src="{{ $logoUrl }}" alt="{{ $alt ?? $brandName.' Logo' }}" class="{{ $imageClass }}">
    </span>
@else
    <span {{ $attributes->merge(['class' => $fallbackBoxClass]) }}>
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
    </span>
@endif
