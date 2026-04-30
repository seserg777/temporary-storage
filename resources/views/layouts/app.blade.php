<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Temporary Storage') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen font-sans antialiased">

    <header class="bg-white shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-xl font-semibold text-gray-800">
                Temporary Storage
            </a>
            <nav class="flex items-center gap-6 text-sm">
                <a href="{{ route('home') }}"
                   class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('home') ? 'font-semibold text-gray-900' : '' }}">
                    Upload
                </a>
                <a href="{{ route('files.index') }}"
                   class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('files.index') ? 'font-semibold text-gray-900' : '' }}">
                    My Files
                </a>
            </nav>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8">
        @if (session('success'))
            <div class="mb-6 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

    @livewireScripts
</body>
</html>