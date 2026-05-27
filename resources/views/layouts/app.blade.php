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
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">

        {{-- Page Heading --}}
        <header class="bg-white shadow">
            <div class="w-full flex flex-wrap justify-between items-center px-4 sm:px-6 lg:px-8 py-3">

                {{-- Logo --}}
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="white">
                            <path d="M12 2C7.6 2 4 5.6 4 10c0 2.8 1.4 5.3 3.5 6.8L12 22l4.5-5.2C18.6 15.3 20 12.8 20 10c0-4.4-3.6-8-8-8z"/>
                            <circle cx="9" cy="9" r="1.5" fill="rgba(255,255,255,0.85)"/>
                            <circle cx="15" cy="9" r="1.5" fill="rgba(255,255,255,0.85)"/>
                        </svg>
                    </div>
                    <span class="font-bold text-xl text-slate-800 tracking-tight">PawMatch</span>
                </a>

                {{-- User actions --}}
                <div class="flex items-center gap-2 sm:gap-4">
                    <form method="GET" action="{{ route('perfil') }}">
                        <button type="submit"
                                class="text-slate-600 hover:text-brand-600 font-bold py-2 px-2 sm:px-4 transition-all flex items-center gap-2 text-sm sm:text-base">
                            Hola {{ Auth::user()->name }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.118a7.5 7.5 0 0115 0v.282a25.046 25.046 0 01-15 0v-.282z" />
                            </svg>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-slate-600 hover:text-red-600 font-bold py-2 px-2 sm:px-4 transition-all flex items-center gap-2 text-sm sm:text-base">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            <span class="hidden sm:inline">Cerrar Sesión</span>
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
</body>
</html>
