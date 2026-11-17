<header
    x-data="navigation"
    x-bind:class="headerClass"
    @keydown.escape.window="close"
    @scroll.window.passive="handleScroll"
    x-init="handleScroll"
    class="sticky top-0 z-40 bg-navy transition-shadow duration-200 motion-reduce:transition-none"
>
    <nav x-bind:class="navClass" aria-label="Main navigation">
        <x-container class="flex items-center justify-between py-4 transition-[padding] duration-200 motion-reduce:transition-none">
        <a href="{{ route('home') }}" class="font-display shrink-0 text-xl font-semibold tracking-wide text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold">
            <span>{{ config('company.name') }}</span>
            <span class="mt-1 block h-0.5 w-12 bg-gold" aria-hidden="true"></span>
        </a>

        <button
            type="button"
            class="rounded-md p-2 text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold lg:hidden"
            x-bind:aria-expanded="open"
            aria-controls="mobile-menu"
            aria-label="Toggle navigation menu"
            @click="toggle"
        >
            <span class="sr-only">Menu</span>
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div class="hidden items-center gap-8 lg:flex">
            <div class="flex items-center gap-6 text-sm font-medium text-white">
                <a class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold" href="{{ route('home') }}">Home</a>
                <a class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold" href="{{ route('home') }}#about">About Us</a>
                <a class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold" href="{{ route('home') }}#education">Education &amp; Training</a>
                <a class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold" href="{{ route('home') }}#refugee-support">Refugee Support</a>
                <a class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold" href="{{ route('home') }}#recruitment">Recruitment</a>
            </div>
            <a href="{{ route('home') }}#contact" class="rounded-xl bg-gold px-4 py-2 text-sm font-semibold text-navy focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">
                Get in touch
            </a>
        </div>
        </x-container>
    </nav>

    <div id="mobile-menu" x-cloak x-bind:class="menuClass" class="border-t border-white/10 lg:hidden">
        <nav aria-label="Mobile navigation">
            <x-container class="flex flex-col gap-4 py-4 text-sm font-medium text-white">
            <a @click="close" href="{{ route('home') }}" class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold">Home</a>
            <a @click="close" href="{{ route('home') }}#about" class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold">About Us</a>
            <a @click="close" href="{{ route('home') }}#education" class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold">Education &amp; Training</a>
            <a @click="close" href="{{ route('home') }}#refugee-support" class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold">Refugee Support</a>
            <a @click="close" href="{{ route('home') }}#recruitment" class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold">Recruitment</a>
            <a @click="close" href="{{ route('home') }}#contact" class="w-fit rounded-xl bg-gold px-4 py-2 text-navy focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">Get in touch</a>
            </x-container>
        </nav>
    </div>
</header>
