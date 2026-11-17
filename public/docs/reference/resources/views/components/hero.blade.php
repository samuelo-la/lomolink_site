@props([
    'image',
    'alt',
])

@php
    $imagePath = public_path(ltrim($image, '/'));
    $imageExists = is_file($imagePath);
@endphp

<section class="relative overflow-hidden bg-navy text-white">
    <x-container class="py-12 lg:grid lg:min-h-[480px] lg:grid-cols-[48%_52%] lg:items-center lg:py-20">
        <div class="w-full lg:pr-8">
            <p class="mb-4 whitespace-nowrap text-xs font-semibold uppercase tracking-[0.12em] text-gold sm:text-sm">Education · Training · Recruitment</p>
            <h1 class="text-balance font-display text-[clamp(2.25rem,3.6vw,3.5rem)] font-bold leading-[1.05] text-white">
                Take the next step towards your future.
            </h1>
            <p class="mt-5 max-w-full text-sm leading-relaxed text-white/85 md:text-base">
                Personalised guidance to help you explore study options, develop professional skills and find your career direction.
            </p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <x-button href="#education" variant="gold" size="sm" data-todo="page-pending">Explore courses</x-button>
                <x-button :href="\App\Support\Enquiry::url('consultation')" variant="outline" size="sm">Book a consultation</x-button>
            </div>
        </div>
    </x-container>

    <div class="relative mt-8 min-h-[280px] pb-12 lg:absolute lg:inset-y-0 lg:left-[48%] lg:right-0 lg:mt-0 lg:min-h-0 lg:pb-0 lg:[clip-path:polygon(12%_0,100%_0,100%_100%,0_100%)]">
        @if ($imageExists)
            <img class="h-full min-h-[280px] w-full object-cover lg:min-h-0" src="{{ asset($image) }}" alt="{{ $alt }}">
        @else
            <div class="h-full min-h-[280px] w-full bg-teal-pale" aria-hidden="true"></div>
        @endif
    </div>
</section>
