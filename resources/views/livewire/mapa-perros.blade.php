<div class="w-full flex items-start">
    @include('partials.sidebar')

    <div class="flex-1 min-w-0 min-h-[calc(100vh-64px)] flex bg-white">

        {{-- Panel izquierdo --}}
        <div class="w-72 bg-white border-r border-slate-100 flex flex-col flex-shrink-0 z-10">

            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-800 text-lg">Mapa</h2>
                <p class="text-[11px] text-slate-500 uppercase tracking-widest font-semibold mt-0.5 mb-3">
                    Perros activos cerca
                </p>

                {{-- Toggle paseo ahora --}}
                <button wire:click="togglePaseoAhora"
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl border-2 text-sm font-bold transition-all
                               {{ $paseandoAhora
                                    ? 'border-emerald-500 bg-emerald-50 text-emerald-800'
                                    : 'border-slate-200 text-slate-700 hover:border-emerald-400' }}">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $paseandoAhora ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                        <span class="uppercase tracking-wider text-xs">{{ $paseandoAhora ? 'Paseando' : 'Activar paseo' }}</span>
                    </div>
                    <div class="w-10 h-5 rounded-full relative transition-colors {{ $paseandoAhora ? 'bg-emerald-500' : 'bg-slate-300' }}">
                        <div class="w-4 h-4 bg-white rounded-full absolute top-0.5 transition-all shadow-sm
                                    {{ $paseandoAhora ? 'left-5' : 'left-0.5' }}"></div>
                    </div>
                </button>

                {{-- Radio --}}
                <div class="mt-4">
                    <div class="flex justify-between items-center mb-1">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Radio de búsqueda</label>
                        <span class="text-xs font-bold text-brand-600">{{ $radio_km }} km</span>
                    </div>
                    <input type="range" min="1" max="50" step="1" wire:model.live.debounce.300ms="radio_km"
                           class="w-full accent-brand-500">
                </div>
            </div>

            {{-- Flash --}}
            @if(session('success') || session('info'))
            <div class="mx-4 mt-3 px-3 py-2 rounded-lg text-xs font-bold {{ session('success') ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                {{ session('success') ?? session('info') }}
            </div>
            @endif

            {{-- Aviso si no hay ubicación --}}
            <div id="aviso-ubicacion" class="hidden mx-4 mt-3 px-3 py-2 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 ring-1 ring-amber-200">
                📍 Activa la ubicación del navegador para ver las distancias reales.
            </div>

            {{-- Lista --}}
            <div class="flex-1 overflow-y-auto">
                <div class="px-5 py-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                        Cercanos ({{ $perrosCercanos->count() }})
                    </p>

                    @forelse($perrosCercanos as $perro)
                    <a href="{{ $perro->perfil_url }}" class="flex items-center gap-3 py-2.5 border-b border-slate-50 last:border-0 hover:bg-slate-50 -mx-2 px-2 rounded-lg transition-colors">
                        <div class="relative flex-shrink-0">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-xl">
                                🐶
                            </div>
                            @if($perro->activo)
                            <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white"></div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-800">{{ $perro->nombre }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $perro->raza }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-xs font-bold text-brand-600">{{ $perro->compat }}%</p>
                            <p class="text-[10px] text-slate-400">{{ $perro->distancia }}</p>
                        </div>
                    </a>
                    @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No hay perros dentro del radio. Amplíalo arriba.</p>
                    @endforelse
                </div>

                <div class="px-5 py-3 border-t border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Parques caninos</p>
                    @foreach($parques as $parque)
                    <div class="flex items-center gap-2.5 py-2 border-b border-slate-50 last:border-0">
                        <div class="w-7 h-7 bg-emerald-100 rounded-lg flex items-center justify-center text-sm flex-shrink-0">🌳</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 truncate">{{ $parque->nombre }}</p>
                            <p class="text-[10px] text-slate-400">{{ $parque->tipo }}</p>
                        </div>
                        <span class="text-[10px] text-slate-400 flex-shrink-0">{{ $parque->dist }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Mapa --}}
        <div class="flex-1 relative">
            @if($tieneUbicacion)
                <div id="map" wire:ignore class="w-full h-full" style="min-height: calc(100vh - 100px)"></div>

                {{-- Capas --}}
                <div class="absolute top-4 right-4 z-[1000] bg-white border border-slate-200 rounded-xl p-3 shadow-lg">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Capas</p>
                    <label class="flex items-center gap-2 cursor-pointer mb-1.5">
                        <input type="checkbox" wire:model.live="mostrarPerros" class="rounded text-brand-500 focus:ring-brand-500">
                        <span class="text-xs text-slate-700 font-semibold">Perros activos</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model.live="mostrarParques" class="rounded text-brand-500 focus:ring-brand-500">
                        <span class="text-xs text-slate-700 font-semibold">Parques</span>
                    </label>
                </div>

                {{-- Info bottom --}}
                <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-[1000]">
                    <div class="bg-white/95 backdrop-blur-sm rounded-full shadow-xl px-5 py-2.5 flex items-center gap-3 text-sm border border-slate-200">
                        <span>📡</span>
                        <span class="text-slate-700">Radio: <strong>{{ $radio_km }} km</strong></span>
                        <div class="w-px h-4 bg-slate-200"></div>
                        <span class="font-bold text-brand-600">{{ $perrosCercanos->count() }} perros cerca</span>
                    </div>
                </div>
            @else
                {{-- Sin ubicación: pantalla que invita a configurarla --}}
                <div class="w-full h-full flex items-center justify-center p-10 bg-gradient-to-br from-cream-50 via-white to-brand-50" style="min-height: calc(100vh - 100px)">
                    <div class="max-w-md text-center animate-pop-in">
                        <div class="w-24 h-24 mx-auto rounded-full bg-brand-500 flex items-center justify-center text-5xl shadow-xl mb-6 animate-float-slow">
                            📍
                        </div>
                        <h2 class="h-display text-3xl text-ink-800 mb-3">Necesitamos tu ubicación</h2>
                        <p class="text-ink-700/70 text-[15px] mb-7 leading-relaxed">
                            Para mostrarte el mapa con los perros cercanos y los parques caninos a tu alrededor, primero tienes que fijar dónde estás.
                        </p>
                        <a href="{{ route('perfil', ['paso' => 3]) }}" class="btn-primary inline-flex items-center gap-2 px-6 py-3 text-base">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                            </svg>
                            Configurar mi ubicación
                        </a>
                        <p class="text-xs text-ink-700/40 mt-5">
                            Te llevamos al paso de ubicación en tu perfil.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($tieneUbicacion)
    {{-- Recursos del mapa. Todo el JS va en /js/pawmap.js para no romper la
         detección de raíz única de Livewire (DOMDocument mal-parsea las etiquetas
         que irían dentro de los template strings de los popups si fueran inline). --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/pawmap.js') }}"></script>
    <script>window.__pawMapData = @js($mapData); if (window.pawMapInit) window.pawMapInit();</script>
    @endif
</div>
