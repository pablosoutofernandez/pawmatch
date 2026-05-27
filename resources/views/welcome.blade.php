<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PawMatch — Encuentra compañero de paseo para tu perro</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50">

<div class="min-h-screen flex flex-col">

    {{-- Header --}}
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="white">
                        <path d="M12 2C7.6 2 4 5.6 4 10c0 2.8 1.4 5.3 3.5 6.8L12 22l4.5-5.2C18.6 15.3 20 12.8 20 10c0-4.4-3.6-8-8-8z"/>
                    </svg>
                </div>
                <span class="font-bold text-xl text-slate-800">PawMatch</span>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-bold text-slate-700 hover:text-brand-600">
                        Ir al Dashboard →
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-bold text-slate-700 hover:text-brand-600">
                        Iniciar sesión
                    </a>
                    <a href="{{ route('register') }}"
                       class="bg-brand-500 hover:bg-brand-600 text-white font-bold py-2 px-5 rounded-xl text-sm shadow-lg shadow-brand-200 transition-all">
                        Registrarse
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <main class="flex-1 flex items-center">
        <div class="max-w-7xl mx-auto px-6 py-16 grid md:grid-cols-2 gap-12 items-center">
            <div>
                <h1 class="text-5xl md:text-6xl font-extrabold text-slate-800 leading-tight mb-5">
                    Encuentra compañero de paseo <span class="text-brand-500">para tu perro</span>
                </h1>
                <p class="text-lg text-slate-600 mb-8 leading-relaxed">
                    Conecta con otros amantes de los perros en tu barrio. Haz amigos, comparte paseos
                    y asegura que tu mascota socialice en un entorno seguro y divertido.
                </p>
                <div class="flex gap-3 flex-wrap">
                    <a href="{{ route('register') }}"
                       class="bg-brand-500 hover:bg-brand-600 text-white font-bold py-3 px-6 rounded-xl text-base shadow-lg shadow-brand-200 transition-all">
                        Registrarse gratis
                    </a>
                    <a href="#features"
                       class="bg-white hover:bg-slate-50 border-2 border-brand-500 text-brand-600 font-bold py-3 px-6 rounded-xl text-base transition-all">
                        Ver cómo funciona
                    </a>
                </div>
            </div>

            <div class="hidden md:flex items-center justify-center">
                <div class="text-9xl">🐾</div>
            </div>
        </div>
    </main>

    {{-- Features --}}
    <section id="features" class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-3xl font-extrabold text-slate-800 text-center mb-12">Cómo funciona</h2>
            <div class="grid md:grid-cols-3 gap-6">
                @php
                $steps = [
                    ['icon' => '👤', 'title' => 'Crear perfil', 'text' => 'Sube las fotos de tu perro y cuéntanos su personalidad, energía y horarios.'],
                    ['icon' => '🔍', 'title' => 'Encontrar compatibles', 'text' => 'Nuestro algoritmo te muestra perros cercanos compatibles con el tuyo.'],
                    ['icon' => '📍', 'title' => 'Quedar para pasear', 'text' => 'Chatea con otros dueños y organiza encuentros en el parque más cercano.'],
                ];
                @endphp
                @foreach($steps as $step)
                <div class="bg-slate-50 rounded-2xl p-8 text-center border border-slate-100">
                    <div class="w-14 h-14 rounded-2xl bg-brand-100 flex items-center justify-center text-3xl mx-auto mb-4">
                        {{ $step['icon'] }}
                    </div>
                    <h3 class="font-bold text-slate-800 text-lg mb-2">{{ $step['title'] }}</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">{{ $step['text'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-slate-900 text-slate-400 py-8">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <p class="text-xs">© 2024 PawMatch. Proyecto Fin de Ciclo DAW · IES Pazo da Mercé</p>
        </div>
    </footer>
</div>

</body>
</html>
