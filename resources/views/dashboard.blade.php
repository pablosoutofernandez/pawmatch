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

                    {{-- Mis perros --}}
                    <div class="soft-card p-6 animate-fade-up">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="h-display text-xl">{{ $misPerros->count() > 1 ? 'Mis compañeros' : 'Mi compañero' }}</h2>
                            <a href="{{ route('perfil') }}?paso=2" class="text-xs font-bold text-brand-500 hover:text-brand-600">Gestionar →</a>
                        </div>

                        @if($misPerros->count() > 0)
                            <div class="space-y-3">
                                @foreach($misPerros as $p)
                                    <a href="{{ route('ver-perro', $p->id) }}" class="flex items-center gap-4 p-2 -mx-2 rounded-2xl hover:bg-cream-50 transition-colors">
                                        <div class="w-16 h-16 bg-gradient-to-br from-brand-200 to-sage-200 flex items-center justify-center text-2xl flex-shrink-0 shadow-soft rounded-2xl overflow-hidden">
                                            @if($p->foto_principal)
                                                <img src="{{ $p->foto_principal_url }}" class="w-full h-full object-cover" alt="{{ $p->nombre }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                                <span class="hidden w-full h-full items-center justify-center">🐾</span>
                                            @else
                                                🐾
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="h-display text-xl text-ink-900 truncate">{{ $p->nombre }}</h3>
                                            <p class="text-sm text-ink-700/55 truncate">{{ $p->raza }} · {{ $p->edad_texto }}</p>
                                            <div class="flex flex-wrap gap-1.5 mt-1.5">
                                                <span class="pill pill-cream text-[10px]">{{ $p->tamano }}</span>
                                                <span class="pill pill-pink text-[10px]">⚡ {{ $p->energia_texto }}</span>
                                                @if($p->vacunado)<span class="pill pill-sage text-[10px]">✓ Vacunado</span>@endif
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <a href="{{ route('perfil') }}?paso=2" class="block text-center text-xs font-bold text-brand-500 hover:text-brand-600 mt-4 pt-4 border-t border-cream-200">
                                + Añadir otro perro
                            </a>
                        @else
                            <div class="text-center py-6">
                                <div class="text-5xl mb-3 inline-block">🐶</div>
                                <p class="h-display text-lg text-ink-800">Aún no has presentado a tu perro</p>
                                <p class="text-sm text-ink-700/55 mt-1 mb-5">Crea su perfil y empieza a hacer match</p>
                                <a href="{{ route('perfil') }}" class="btn-primary">Añadir mi perro</a>
                            </div>
                        @endif
                    </div>

                    {{-- Mini-mapa con perros cercanos --}}
                    <div class="soft-card overflow-hidden block relative animate-fade-up"
                         style="animation-delay: 70ms; min-height: 260px;">

                        {{-- Mapa Leaflet --}}
                        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
                        <div id="dashboard-mini-map" class="absolute inset-0 w-full h-full" style="z-index:1;"></div>

                        {{-- Badge: paseando ahora --}}
                        <div class="absolute top-3 left-3 z-[1000] flex items-center gap-1.5 bg-white/95 backdrop-blur-sm border border-ink-100 rounded-full px-3 py-1.5 shadow-soft pointer-events-none">
                            <span class="w-2 h-2 rounded-full bg-sage-500 animate-pulse"></span>
                            <span class="text-xs font-bold text-ink-800">{{ $stats['activos_ahora'] }} paseando ahora</span>
                        </div>

                        {{-- CTA esquina inferior: enlace al mapa completo --}}
                        <a href="{{ route('mapa') }}"
                           class="absolute bottom-3 right-3 z-[1000] group flex items-center gap-2
                                  bg-white/95 backdrop-blur-sm px-3 py-2 rounded-xl shadow-soft
                                  border border-ink-100 hover:bg-brand-50 hover:border-brand-200 transition-all">
                            <span class="text-xs font-bold text-ink-800 group-hover:text-brand-700">Ver mapa completo</span>
                            <svg class="w-3.5 h-3.5 text-ink-400 group-hover:text-brand-500 group-hover:translate-x-0.5 transition-all"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                            </svg>
                        </a>

                        @if(!$miniMapData['tiene_ubicacion'])
                        <div class="absolute bottom-3 left-3 z-[1000] flex items-center gap-1.5 bg-amber-50/95 backdrop-blur-sm border border-amber-200 rounded-xl px-3 py-1.5 shadow-soft pointer-events-none">
                            <span class="text-xs font-semibold text-amber-700">📍 Añade tu ubicación para ver perros cerca</span>
                        </div>
                        @endif
                    </div>

                    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                    <script>
                    (function () {
                        const data = @json($miniMapData);

                        // Espera a que Leaflet esté cargado
                        function initDashMap () {
                            if (typeof L === 'undefined') { setTimeout(initDashMap, 80); return; }

                            const el = document.getElementById('dashboard-mini-map');
                            if (!el || el._leaflet_id) return;

                            const zoom = data.tiene_ubicacion ? 13 : 6;
                            const map  = L.map(el, {
                                zoomControl: false,
                                attributionControl: false,
                                dragging: false,
                                scrollWheelZoom: false,
                                doubleClickZoom: false,
                                touchZoom: false,
                                keyboard: false,
                            }).setView([data.centro_lat, data.centro_lng], zoom);

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                            }).addTo(map);

                            // Marcador propio (estrella)
                            if (data.tiene_ubicacion) {
                                const youIcon = L.divIcon({
                                    className: '',
                                    html: `<div style="
                                        width:36px;height:36px;border-radius:50%;
                                        background:#7f9a73;border:3px solid white;
                                        box-shadow:0 2px 8px rgba(0,0,0,.25);
                                        display:flex;align-items:center;justify-content:center;
                                        font-size:16px;line-height:1;">🐾</div>`,
                                    iconSize: [36, 36],
                                    iconAnchor: [18, 18],
                                });
                                L.marker([data.centro_lat, data.centro_lng], { icon: youIcon }).addTo(map);
                            }

                            // Agrupar perros cercanos para evitar saturación
                            // Cuadrícula de ~200m: agrupa puntos dentro del mismo tile
                            const GRID = 0.002; // ~200 m en grados
                            const clusters = {};
                            data.perros.forEach(p => {
                                const gx = Math.round(p.lat / GRID);
                                const gy = Math.round(p.lng / GRID);
                                const key = `${gx}_${gy}`;
                                if (!clusters[key]) clusters[key] = { lat: p.lat, lng: p.lng, perros: [], activos: 0 };
                                clusters[key].perros.push(p.nombre);
                                if (p.activo) clusters[key].activos++;
                            });

                            Object.values(clusters).forEach(c => {
                                const n      = c.perros.length;
                                const activo = c.activos > 0;
                                const size   = n === 1 ? 32 : n <= 3 ? 38 : 46;
                                const bg     = activo ? '#5f7d53' : '#c08f7a';
                                const label  = n === 1
                                    ? `<span style="font-size:15px">🐶</span>`
                                    : `<span style="font-size:11px;font-weight:700;color:white">${n}</span>`;

                                const icon = L.divIcon({
                                    className: '',
                                    html: `<div style="
                                        width:${size}px;height:${size}px;border-radius:50%;
                                        background:${bg};border:2.5px solid white;
                                        box-shadow:0 2px 8px rgba(0,0,0,.2);
                                        display:flex;align-items:center;justify-content:center;
                                        cursor:pointer;transition:transform .15s;">
                                        ${label}
                                        ${activo ? `<div style="position:absolute;top:-3px;right:-3px;width:10px;height:10px;border-radius:50%;background:#4ade80;border:2px solid white"></div>` : ''}
                                    </div>`,
                                    iconSize: [size, size],
                                    iconAnchor: [size / 2, size / 2],
                                });

                                const nombres = c.perros.slice(0, 4).join(', ') + (n > 4 ? ` +${n - 4}` : '');
                                L.marker([c.lat, c.lng], { icon })
                                    .bindTooltip(n === 1 ? c.perros[0] : `${n} perros: ${nombres}`, {
                                        direction: 'top',
                                        offset: [0, -(size / 2) - 4],
                                        className: 'paw-tooltip',
                                    })
                                    .addTo(map);
                            });
                        }

                        if (document.readyState === 'complete') { initDashMap(); }
                        else { window.addEventListener('load', initDashMap); }
                    })();
                    </script>
                    <style>
                        .paw-tooltip {
                            background: rgba(255,255,255,.96);
                            border: 1px solid #e8ddd5;
                            border-radius: 10px;
                            box-shadow: 0 4px 14px rgba(0,0,0,.12);
                            font-size: 12px;
                            font-weight: 600;
                            color: #2d2520;
                            padding: 5px 10px;
                            white-space: nowrap;
                        }
                        .paw-tooltip::before { display: none; }
                    </style>

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
                                        <p class="text-xs text-ink-700/55 truncate">{{ $perro->raza }} · {{ $perro->dueno->name }}@if($perro->dueno->es_premium) <x-premium-badge />@endif</p>
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