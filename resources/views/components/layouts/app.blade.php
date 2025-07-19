<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scrollbar-thumb-rounded-full scrollbar-track-rounded-full scrollbar-thumb-sky-700 scrollbar-track-sky-300 scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-200 flex flex-col">

    {{-- HEADLINE --}}
    <x-app-headline />

    {{-- NAV BAR --}}
    <div class="w-full bg-base-100">
        <div class="max-w-7xl mx-auto flex items-center">

            {{-- APP BRANDING --}}
                {{-- Centering of navbar menu can be achieved by editing the w-XX properties of app branding thereafter --}}
                <x-app-brand class="w-66 lg:w-44 xl:w-62 2xl:w-66 pl-5 sm:pl-14 lg:pl-10"/>

            {{-- NAVBAR --}}
            <div class="flex-1">
                <livewire:app_layout-navbar />
            </div>
        </div>
    </div>

    {{-- MAIN LAYOUT CONTAINER --}}
    <div class="bg-base-200 flex flex-1">
        {{-- MAIN CONTENT AREA --}}
        <div class="max-w-7xl mx-auto flex-1">
            <x-main with-nav full-width>
                <x-slot:content class="mt-8 mb-20 px-5 sm:px-15 lg:px-10">
                    
                    {{-- Breadcrumbs --}}
                    <livewire:app_layout-breadcrumbs :title="$title" />

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