<footer class="bg-navy text-white">
    <x-container class="flex flex-col gap-4 py-8 text-sm lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="font-semibold">{{ config('company.name') }}</p>
            <p class="mt-1 text-white/80">{{ config('company.email') }}</p>
        </div>
        <p class="text-white/80">&copy; {{ date('Y') }} {{ config('company.name') }}. All rights reserved.</p>
    </x-container>
</footer>
