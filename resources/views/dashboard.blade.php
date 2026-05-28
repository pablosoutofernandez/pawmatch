<x-app-layout>
    <div class="w-full flex items-start">
        @include('partials.sidebar')

        <div class="flex-1 min-w-0 min-h-screen pt-6 md:pt-10 px-4 sm:px-6 lg:px-10 pb-16">

            {{-- Flash --}}
            @if(session('success'))
                <div class="w-full max-w-5xl mx-auto mb-6 flex items-center gap-3 bg-sage-50 ring-1 ring-sage-200 text-sage-700 px-5 py-3.5 rounded-2xl shadow-soft animate-fade-in-down">
                    <span class="text-lg">🎉</span>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            <div class="w-full max-w-6xl mx-auto space-y-6">

                {{-- ── FILA 1: Saludo + Imagen hero (Optimizado a WebP + Prioridad de carga) ── --}}
                <div class="relative overflow-hidden soft-card p-0 animate-fade-up">
                    {{-- Imagen de fondo con overlay degradado --}}
                    <div class="absolute inset-0">
                        <img src="/img/hero-dogs.png" alt=""
                             class="w-full h-full object-cover object-center"
                             fetchpriority="high">
                        <div class="absolute inset-0 bg-gradient-to-r from-white/95 via-white/80 to-white/10"></div>
                    </div>

                    {{-- Contenido sobre la imagen --}}
                    <div class="relative z-10 px-8 py-10 max-w-lg">
                        <h1 class="h-display text-4xl sm:text-5xl leading-[1.05]">
                            ¡Hola,<br><span class="text-brand-500">{{ explode(' ', $user->name)[0] }}</span>!
                        </h1>
                        <p class="text-ink-700/65 mt-3 text-[15px] max-w-xs leading-relaxed">
                            Esto es lo que pasa en tu zona hoy.
                        </p>
                    </div>
                </div>

                {{-- ── FILA 2: Mi perro + Mini-mapa + Premium ── --}}
                <div class="grid lg:grid-cols-3 gap-5">

                    {{-- Mi perro (Optimizado: Bordes estándar y sin animaciones infinitas pesadas) --}}
                    <div class="soft-card p-6 animate-fade-up">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="h-display text-xl">Mi compañero</h2>
                            <a href="{{ route('perfil') }}" class="text-xs font-bold text-brand-500 hover:text-brand-600">Editar →</a>
                        </div>

                        @if($miPerro)
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 bg-gradient-to-br from-brand-200 to-sage-200 flex items-center justify-center text-4xl flex-shrink-0 shadow-soft rounded-2xl">
                                    🐾
                                </div>
                                <div class="min-w-0">
                                    <h3 class="h-display text-2xl text-ink-900">{{ $miPerro->nombre }}</h3>
                                    <p class="text-sm text-ink-700/55">{{ $miPerro->raza }} · {{ $miPerro->edad_texto }}</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2 mt-4">
                                <span class="pill pill-cream">{{ $miPerro->tamano }}</span>
                                <span class="pill pill-pink">⚡ {{ $miPerro->energia_texto }}</span>
                                @if($miPerro->vacunado)<span class="pill pill-sage">✓ Vacunado</span>@endif
                            </div>
                            @if($miPerro->caracter)
                                <div class="flex flex-wrap gap-1.5 mt-3 pt-4 border-t border-cream-200">
                                    @foreach($miPerro->caracter as $rasgo)
                                        <span class="text-[12px] font-medium px-3 py-1 rounded-full ring-1 ring-brand-200 text-brand-600 bg-brand-50 capitalize">{{ $rasgo }}</span>
                                    @endforeach
                                </div>
                            @endif

                        @else
                            <div class="text-center py-6">
                                <div class="text-5xl mb-3 inline-block">🐶</div>
                                <p class="h-display text-lg text-ink-800">Aún no has presentado a tu perro</p>
                                <p class="text-sm text-ink-700/55 mt-1 mb-5">Crea su perfil y empieza a hacer match</p>
                                <a href="{{ route('perfil') }}" class="btn-primary">Añadir mi perro</a>
                            </div>
                        @endif
                    </div>

                    {{-- Mini-mapa (Mantiene el iframe original, pero optimiza los elementos flotantes) --}}
                    <a href="{{ route('mapa') }}"
                       class="soft-card overflow-hidden block group relative animate-fade-up cursor-pointer"
                       style="animation-delay: 70ms; min-height: 220px;">

                        {{-- [IFRAME INTACTO] Fondo simulado de mapa (tiles OSM estáticos) --}}
                        <iframe
                                src="https://www.openstreetmap.org/export/embed.html?bbox=-8.75,42.18,-8.70,42.22&layer=mapnik"
                                class="absolute inset-0 w-full h-full border-0 pointer-events-none"
                                loading="lazy"
                                title="Mini mapa">
                        </iframe>

                        {{-- Overlay suave con CTA --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-ink-900/60 via-transparent to-transparent
                                    group-hover:from-ink-900/70 transition-all duration-300">
                        </div>

                        <div class="absolute top-3 left-3 flex items-center gap-1.5 bg-white border border-ink-100 rounded-full px-3 py-1.5 shadow-soft">
                            <span class="w-2 h-2 rounded-full bg-sage-500 opacity-90"></span>
                            <span class="text-xs font-bold text-ink-800">{{ $stats['activos_ahora'] }} paseando ahora</span>
                        </div>

                        <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between">
                            <div>
                                <p class="h-display text-white text-lg leading-tight">Ver el mapa</p>
                                <p class="text-white/75 text-xs mt-0.5">Perros cerca de ti</p>
                            </div>
                            <div class="w-9 h-9 bg-white/30 rounded-full flex items-center justify-center
                                        group-hover:bg-white/40 group-hover:scale-110 transition-all">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                </svg>
                            </div>
                        </div>
                    </a>

                    {{-- Banner Premium --}}
                    @if(auth()->user()->plan === 'free')
                        <div class="relative overflow-hidden rounded-[1.75rem] shadow-lift animate-fade-up"
                             style="animation-delay: 140ms;
                                background: linear-gradient(135deg, #7f9a73 0%, #5f7d53 40%, #cf5f80 100%);">

                            {{-- Huellas decorativas --}}
                            <div class="absolute -right-4 -top-4 text-[8rem] opacity-15 rotate-12 select-none leading-none">🐾</div>
                            <div class="absolute right-8 bottom-8 text-5xl opacity-10 select-none">✦</div>

                            <div class="relative z-10 p-7 flex flex-col h-full justify-between">
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-widest text-white/60">PawMatch Club</span>
                                    <h3 class="h-display text-2xl text-white mt-1 leading-snug">
                                        Más paseos,<br>más amigos.
                                    </h3>
                                    <ul class="mt-4 space-y-2">
                                        @foreach([
                                            ['🐕', 'Quedadas de grupo organizadas'],
                                            ['🏅', 'Insignias y logros de comunidad'],
                                            ['🎓', 'Consejos de adiestradores reales'],
                                            ['💬', 'Chat sin límite de mensajes'],
                                        ] as $f)
                                            <li class="flex items-center gap-2.5 text-[13px] text-white/85">
                                                <span class="text-base leading-none">{{ $f[0] }}</span>
                                                {{ $f[1] }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="mt-6">
                                    <button class="w-full bg-white text-sage-700 font-bold text-sm rounded-full py-2.5
                                               hover:bg-cream-50 active:scale-95 transition-all shadow-soft">
                                        Únete por 4,99 €/mes
                                    </button>
                                    <p class="text-center text-[11px] text-white/50 mt-2">Sin permanencia · Cancela cuando quieras</p>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- ── FILA 3: Compatibles cerca ── --}}
                <div class="soft-card p-6 animate-fade-up" style="animation-delay: 100ms">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="h-display text-xl">Compatibles cerca</h2>
                        <a href="{{ route('discover') }}" class="text-xs font-bold text-brand-500 hover:text-brand-600">Ver todos →</a>
                    </div>

                    @if($sugerencias->isEmpty())
                        <div class="text-center py-10 text-ink-700/50">
                            <div class="text-4xl mb-2">🌱</div>
                            <p class="text-sm">Todavía no hay perros cerca. ¡Vuelve pronto!</p>
                        </div>
                    @else
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach($sugerencias as $perro)
                                <div class="flex items-center gap-3 p-3 rounded-2xl hover:bg-cream-100/70 transition-colors">
                                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-cream-200 to-cream-300 flex items-center justify-center text-xl flex-shrink-0">🐶</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-baseline gap-1.5">
                                            <span class="font-bold text-ink-800 text-sm">{{ $perro->nombre }}</span>
                                            <span class="text-[11px] text-ink-700/40">{{ $perro->distancia }}</span>
                                        </div>
                                        <p class="text-xs text-ink-700/55 truncate">{{ $perro->raza }} · {{ $perro->dueno->name }}</p>
                                        <div class="mt-1.5 compat-bar w-24">
                                            <div class="compat-fill" style="width: {{ $perro->compatibilidad }}%"></div>
                                        </div>
                                    </div>
                                    <span class="text-sm font-bold {{ $perro->compatibilidad >= 80 ? 'text-sage-600' : 'text-brand-500' }} flex-shrink-0">
                                {{ $perro->compatibilidad }}%
                            </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>