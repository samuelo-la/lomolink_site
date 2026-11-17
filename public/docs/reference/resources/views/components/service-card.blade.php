@props([
    'icon',
    'title',
    'text',
    'href',
    'linkText' => 'Learn more',
])

<article class="flex h-full items-start gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-transform hover:-translate-y-1 hover:shadow-md motion-reduce:transform-none">
    <div class="shrink-0 text-teal">
        <x-dynamic-component :component="'icons.'.$icon" />
    </div>
    <div class="min-w-0">
        <h3 class="font-display text-lg font-bold leading-tight text-navy">{{ $title }}</h3>
        <p class="mt-2 text-sm leading-snug text-dark-grey">{{ $text }}</p>
        <a
            href="{{ $href }}"
            {{ $attributes->merge(['class' => 'mt-4 inline-flex w-fit items-center text-sm font-semibold text-teal underline decoration-transparent underline-offset-4 transition-colors hover:decoration-current focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal']) }}
        >
            {{ $linkText }}
            <span class="ml-2 text-base" aria-hidden="true">→</span>
        </a>
    </div>
</article>
