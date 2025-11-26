<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
	
	@include('partials.theme-init-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-200">
    
    {{-- The navbar with `sticky` and `full-width` --}}
    <x-nav sticky full-width>
        <x-slot:brand> 
            {{-- Brand --}}
            <a href="{{ url()->route('home') }}">
                <x-icon name="phosphor.arrow-circle-left" class="cursor-pointer" label="Home" />
            </a>
        </x-slot:brand>
    </x-nav>

    <x-main full-width>
        <x-slot:content class="mt-0 mb-20">
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{-- Toast --}}
    <x-toast />                    
    
    {{-- Theme toggle --}}
    <x-theme-toggle class="hidden" />
</body>
</html>