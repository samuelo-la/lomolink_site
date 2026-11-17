@props([
    'href' => '#',
    'variant' => 'teal',
    'size' => 'md',
])

@php
    $classes = [
        'gold' => 'bg-gold text-navy hover:bg-gold/90 focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-navy',
        'teal' => 'bg-teal text-white hover:bg-teal/90 focus-visible:ring-2 focus-visible:ring-navy',
        'outline' => 'border border-white text-white hover:bg-white/10 focus-visible:ring-2 focus-visible:ring-gold',
    ];
    $external = str_starts_with($href, 'http://') || str_starts_with($href, 'https://');
    $variantClasses = $classes[$variant] ?? $classes['teal'];
    $sizes = [
        'md' => 'px-6 py-3 text-base',
        'sm' => 'px-4 py-2 text-sm',
    ];
    $sizeClasses = $sizes[$size] ?? $sizes['md'];
@endphp

<a
    href="{{ $href }}"
    @if ($external)
        target="_blank"
        rel="noopener noreferrer"
    @endif
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-xl font-semibold whitespace-nowrap text-center transition-colors focus-visible:outline-none '.$sizeClasses.' '.$variantClasses]) }}
>
    {{ $slot }}@if ($external) <span class="sr-only"> (opens in a new tab)</span>@endif
</a>
