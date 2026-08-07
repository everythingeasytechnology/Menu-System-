@props([
    'variant' => 'default', // 'default' (white), 'tint' (gray-tint), 'warm' (peach-warm)
    'hoverable' => false,
])

@php
    $bgClass = match($variant) {
        'tint' => 'bg-card-tint border border-border/80',
        'warm' => 'bg-card-warm border border-orange/15',
        default => 'bg-card border border-border',
    };
    
    $hoverClass = $hoverable 
        ? 'hover:shadow-md hover:shadow-navy/5 hover:-translate-y-0.5 cursor-pointer active:translate-y-0' 
        : '';
@endphp

<div {{ $attributes->merge(['class' => "$bgClass rounded-card p-6 shadow-sm transition-all duration-300 $hoverClass"]) }}>
    {{ $slot }}
</div>
