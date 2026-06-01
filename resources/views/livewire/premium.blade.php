<div class="w-full flex items-start">
    @include('partials.sidebar')

    <div class="flex-1 min-w-0 min-h-screen pt-6 md:pt-10 px-4 sm:px-6 lg:px-10 pb-16">

        {{-- Flash --}}
        @if(session('success'))
            <div class="w-full max-w-3xl mx-auto mb-6 flex items-center gap-3 bg-sage-50 ring-1 ring-sage-200 text-sage-700 px-5 py-3.5 rounded-2xl shadow-soft animate-fade-in-down">
                <span class="text-lg">🎉</span>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('info'))
            <div class="w-full max-w-3xl mx-auto mb-6 flex items-center gap-3 bg-cream-100 ring-1 ring-cream-300 text-ink-700 px-5 py-3.5 rounded-2xl shadow-soft animate-fade-in-down">
                <span class="text-sm font-semibold">{{ session('info') }}</span>
            </div>
        @endif

        <div class="w-full max-w-3xl mx-auto space-y-7">

            {{-- Header --}}
            <div class="animate-fade-up">
                <h1 class="h-display text-4xl sm:text-5xl leading-none">PawMatch Premium</h1>
                <p class="text-ink-700/60 mt-2 text-[15px]">Más matches, más alcance, más paseos. Sin ataduras.</p>
            </div>

            {{-- Estado actual --}}
            <div class="soft-card-flat p-6 animate-fade-up" style="animation-delay:60ms">
                @if($esPremium)
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-brand-100 text-brand-600 text-2xl">★</span>
                        <div>
                            <p class="font-bold text-ink-900 text-lg">Tu plan: Premium</p>
                            <p class="text-sm text-ink-700/60">
                                Matches ilimitados activos.
                                @if($expira) Renueva el {{ $expira->format('d/m/Y') }}. @endif
                            </p>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-cream-100 text-ink-700/60 text-2xl">🐾</span>
                        <div>
                            <p class="font-bold text-ink-900 text-lg">Tu plan: Gratuito</p>
                            <p class="text-sm text-ink-700/60">
                                Usas {{ $matchesActivos }} de {{ $limite }} matches.
                                Te {{ $matchesRestantes === 1 ? 'queda' : 'quedan' }}
                                <span class="font-semibold text-brand-600">{{ $matchesRestantes }}</span>
                                {{ $matchesRestantes === 1 ? 'match libre' : 'matches libres' }}.
                            </p>
                        </div>
                    </div>
                    {{-- Barra de uso --}}
                    <div class="mt-4 h-2.5 w-full bg-cream-100 rounded-full overflow-hidden">
                        <div class="h-full bg-brand-500 rounded-full transition-all"
                             style="width: {{ $limite > 0 ? min(100, round($matchesActivos / $limite * 100)) : 0 }}%"></div>
                    </div>
                @endif
            </div>

            {{-- Ventajas --}}
            <div class="grid sm:grid-cols-2 gap-4 animate-fade-up" style="animation-delay:120ms">
                @php
                    $ventajas = [
                        ['t' => 'Matches ilimitados', 'd' => 'Habla con todos los perros que quieras, sin el tope de '.$limite.' del plan gratuito.'],
                        ['t' => 'Radio ampliado', 'd' => 'Busca compañeros de paseo hasta a 50 km a la redonda.'],
                        ['t' => 'Ver quién te ha dado like', 'd' => 'Descubre a quién le gusta tu perro antes de corresponder.'],
                        ['t' => 'Perfil destacado', 'd' => 'Tu perro aparece antes en Descubrir y en el mapa.'],
                    ];
                @endphp
                @foreach($ventajas as $v)
                    <div class="soft-card-flat p-5">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 inline-flex items-center justify-center w-7 h-7 rounded-lg bg-sage-50 text-sage-700 ring-1 ring-sage-200 text-sm font-bold">✓</span>
                            <div>
                                <p class="font-semibold text-ink-900">{{ $v['t'] }}</p>
                                <p class="text-sm text-ink-700/60 mt-0.5">{{ $v['d'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Acción --}}
            <div class="soft-card-flat p-6 text-center animate-fade-up" style="animation-delay:180ms">
                @if($esPremium)
                    <p class="text-ink-700/70 text-sm mb-4">Ya disfrutas de todas las ventajas Premium. ¡Gracias por apoyar PawMatch! 🐶</p>
                    <button wire:click="cancelar"
                            class="px-5 py-2.5 rounded-2xl text-sm font-semibold text-ink-700/70 ring-1 ring-cream-300 hover:bg-cream-50 transition">
                        Volver al plan gratuito
                    </button>
                @else
                    <p class="text-2xl font-bold text-ink-900">4,99 € <span class="text-base font-normal text-ink-700/50">/ mes</span></p>
                    <p class="text-ink-700/60 text-sm mt-1 mb-4">Cancela cuando quieras.</p>
                    <button wire:click="activar"
                            class="inline-flex items-center gap-2 px-7 py-3 rounded-2xl text-sm font-bold text-white bg-brand-500 hover:bg-brand-600 shadow-soft transition">
                        <span>★</span> Hazte Premium
                    </button>
                    <p class="text-[11px] text-ink-700/40 mt-3">Demostración académica: la activación es inmediata y no requiere pago real.</p>
                @endif
            </div>

        </div>
    </div>
</div>
