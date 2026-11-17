@props([
    'eyebrow' => null,
    'title',
])

<div {{ $attributes->merge(['class' => 'text-center']) }}>
    @if ($eyebrow)
        <p class="mb-2 text-sm font-semibold uppercase tracking-widest text-gold">{{ $eyebrow }}</p>
    @endif
    <h2 class="font-display text-3xl font-bold text-navy md:text-4xl">{{ $title }}</h2>
    <span class="mx-auto mt-3 block h-[3px] w-12 bg-gold" aria-hidden="true"></span>
</div>
