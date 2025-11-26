<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scrollbar scrollbar-thumb-rounded-full scrollbar-track-rounded-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO Meta Tags: it will includes <title> --}}
	{!! SEO::generate(true) !!}

    {{-- Apple Touch Icon --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
	
	@include('partials.theme-init-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-200 flex flex-col">

    {{-- HEADLINE --}}
    <x-app-headline />

    {{-- NAV BAR --}}
    <div class="w-full bg-base-100 sticky top-0 z-40">
        <div class="mx-auto flex items-center">
            {{-- NAVBAR --}}
            <div class="flex-1">
                <livewire:app_layout-navbar />
            </div>
        </div>
    </div>

    {{-- MAIN LAYOUT CONTAINER --}}
    <div class="bg-base-200 flex flex-1">
        {{-- MAIN CONTENT AREA --}}
        <div class="mx-auto flex-1">
            <x-main with-nav full-width class="!p-0">
                <x-slot:content>
                    {{-- Breadcrumbs --}}
                    <livewire:app_layout-breadcrumbs />

                    {{-- Page App --}}
                    {{ $slot }}
                </x-slot:content>
            </x-main>
        </div>
    </div>

    {{-- FOOTER - Takes full width --}}
    <livewire:app_layout-footer />

    {{-- TOAST area --}}
    <x-toast />

</body>
</html>