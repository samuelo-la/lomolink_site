@extends('layouts.app')

@section('title', config('company.name'))

@section('content')
    <x-hero image="images/home-hero.webp" alt="Learner preparing for their next step in education and training." />

    <section id="how-we-can-help" class="bg-white">
        <x-container class="py-12 lg:py-20">
            <x-section-heading title="How we can help" />

            <div class="mt-12 grid gap-6 lg:grid-cols-3">
                <x-service-card
                    icon="education"
                    title="Education guidance"
                    text="Explore suitable study options and get support with your application."
                    href="/about"
                    data-todo="page-pending"
                />
                <x-service-card
                    icon="training"
                    title="Training and qualifications"
                    text="Explore courses to develop your skills and support your career goals."
                    href="/study-options"
                    data-todo="page-pending"
                />
                <x-service-card
                    icon="healthcare"
                    title="Healthcare recruitment"
                    text="Support for candidates and employers seeking the right match."
                    href="/recruitment"
                    data-todo="page-pending"
                />
            </x-container>
        </div>
    </section>
@endsection
