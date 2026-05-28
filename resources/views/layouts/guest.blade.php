<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PawMatch') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink-800 antialiased">
        <div class="min-h-screen paw-canvas flex flex-col lg:flex-row">

            {{-- Panel lateral decorativo (solo desktop) --}}
            <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden
                        bg-gradient-to-br from-brand-400 via-brand-500 to-sage-600 text-white p-14 flex-col justify-between">
                <div class="absolute -right-16 -top-16 text-[16rem] opacity-15 rotate-12 select-none animate-float-slow">🐾</div>
                <div class="absolute right-20 bottom-24 text-9xl opacity-15 select-none">🐶</div>

                <a href="/" class="flex items-center gap-3 relative z-10">
                    <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="white">
                            <path d="M12 2C7.6 2 4 5.6 4 10c0 2.8 1.4 5.3 3.5 6.8L12 22l4.5-5.2C18.6 15.3 20 12.8 20 10c0-4.4-3.6-8-8-8z"/>
                        </svg>
                    </div>
                    <span class="font-display font-semibold text-2xl">PawMatch</span>
                </a>

                <div class="relative z-10">
                    <h2 class="font-display font-semibold text-5xl leading-[1.05]">
                        Cada paseo<br>es una nueva<br>amistad.
                    </h2>
                    <p class="mt-5 text-white/80 text-lg max-w-sm leading-relaxed">
                        Conecta con dueños de tu barrio y deja que tu perro encuentre a su mejor compañero de aventuras.
                    </p>
                </div>

                <p class="relative z-10 text-white/60 text-sm">🐾 Más de mil colas felices y subiendo</p>
            </div>

            {{-- Panel del formulario --}}
            <div class="flex-1 flex flex-col justify-center items-center px-6 py-12">
                {{-- Logo móvil --}}
                <a href="/" class="lg:hidden flex items-center gap-2.5 mb-8">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center shadow-soft">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                            <path d="M12 2C7.6 2 4 5.6 4 10c0 2.8 1.4 5.3 3.5 6.8L12 22l4.5-5.2C18.6 15.3 20 12.8 20 10c0-4.4-3.6-8-8-8z"/>
                        </svg>
                    </div>
                    <span class="h-display text-2xl">PawMatch</span>
                </a>

                <div class="w-full max-w-md soft-card p-8 sm:p-10 animate-fade-up">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
