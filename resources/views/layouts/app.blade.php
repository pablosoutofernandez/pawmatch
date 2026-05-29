<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PawMatch') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased text-ink-800">
    <div class="min-h-screen paw-canvas">

        {{-- Page Heading --}}
        <header class="sticky top-0 z-30">
            <div class="w-full flex flex-wrap justify-between items-center px-4 sm:px-6 lg:px-8 py-3
                        bg-cream-50/80 backdrop-blur-md border-b border-cream-200">

                {{-- Logo --}}
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center shadow-soft group-hover:rotate-6 transition-transform">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="white">
                            <path d="M12 2C7.6 2 4 5.6 4 10c0 2.8 1.4 5.3 3.5 6.8L12 22l4.5-5.2C18.6 15.3 20 12.8 20 10c0-4.4-3.6-8-8-8z"/>
                            <circle cx="9" cy="9" r="1.5" fill="rgba(255,255,255,0.85)"/>
                            <circle cx="15" cy="9" r="1.5" fill="rgba(255,255,255,0.85)"/>
                        </svg>
                    </div>
                    <span class="h-display text-2xl text-ink-900">PawMatch</span>
                </a>

                {{-- User actions --}}
                <div class="flex items-center gap-2 sm:gap-3">

                    {{-- Perfil: chip cálido con avatar --}}
                    <form method="GET" action="{{ route('mi-perfil') }}">
                        <button type="submit"
                                class="group flex items-center gap-2.5 pl-1.5 pr-3 sm:pr-4 py-1.5 rounded-full
                                       bg-white/70 ring-1 ring-brand-100 hover:ring-brand-300 hover:bg-white
                                       transition-all active:scale-95">
                            <span class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-300 to-brand-500 text-white
                                         flex items-center justify-center text-sm font-bold shadow-sm group-hover:scale-105 transition-transform">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <span class="text-sm font-semibold text-ink-700 hidden sm:inline">
                                {{ explode(' ', Auth::user()->name)[0] }}
                            </span>
                        </button>
                    </form>

                    {{-- Logout: icono suave --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Cerrar sesión"
                                class="w-10 h-10 flex items-center justify-center rounded-full
                                       bg-white/60 text-ink-700/60 ring-1 ring-cream-300
                                       hover:text-brand-600 hover:ring-brand-200 hover:bg-white transition-all active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
    @include('partials.geolocate')
</body>
</html>
