<div class="w-full grid grid-cols-12 justify-start items-start gap-4">
    @include('partials.sidebar')

    <div class="w-full col-span-12 md:col-span-11 min-h-[calc(100vh-64px)] flex bg-white">

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
            </div>

            {{-- Flash --}}
            @if(session('success') || session('info'))
            <div class="mx-4 mt-3 px-3 py-2 rounded-lg text-xs font-bold {{ session('success') ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                {{ session('success') ?? session('info') }}
            </div>
            @endif

            {{-- Lista --}}
            <div class="flex-1 overflow-y-auto">
                <div class="px-5 py-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                        Activos cercanos ({{ $perrosCercanos->count() }})
                    </p>

                    @foreach($perrosCercanos as $perro)
                    <div class="flex items-center gap-3 py-2.5 border-b border-slate-50 last:border-0">
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
                    </div>
                    @endforeach
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
        <div class="flex-1 relative" wire:ignore>
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
            <div id="map" class="w-full h-full" style="min-height: calc(100vh - 100px)"></div>

            {{-- Capas --}}
            <div class="absolute top-4 right-4 z-20 bg-white border border-slate-200 rounded-xl p-3 shadow-lg">
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
            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20">
                <div class="bg-white/95 backdrop-blur-sm rounded-full shadow-xl px-5 py-2.5 flex items-center gap-3 text-sm border border-slate-200">
                    <span>📡</span>
                    <span class="text-slate-700">Radio: <strong>{{ $radio_km }} km</strong></span>
                    <div class="w-px h-4 bg-slate-200"></div>
                    <span class="font-bold text-brand-600">{{ $perrosCercanos->count() }} perros activos</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('livewire:navigated', initMap);
document.addEventListener('DOMContentLoaded', initMap);

function initMap() {
    const el = document.getElementById('map');
    if (!el || el._initialized) return;

    const map = L.map('map', { zoomControl: false }).setView([40.4168, -3.7038], 14);
    el._initialized = true;

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OSM &copy; CARTO',
        subdomains: 'abcd', maxZoom: 20
    }).addTo(map);

    L.control.zoom({ position: 'bottomright' }).addTo(map);

    const makeIcon = (compat) => L.divIcon({
        className: '',
        html: `<div style="background:#f02d5e;color:#fff;border-radius:50%;width:42px;height:42px;
               display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;
               box-shadow:0 4px 14px rgba(240,45,94,0.4);border:2px solid #fff;cursor:pointer">${compat}%</div>`,
        iconSize: [42, 42], iconAnchor: [21, 21],
    });

    const perros = [
        {lat:40.419,lng:-3.701,name:'Rocky',breed:'Labrador',compat:94},
        {lat:40.415,lng:-3.706,name:'Luna',breed:'Beagle',compat:87},
        {lat:40.421,lng:-3.712,name:'Max',breed:'Golden',compat:91},
        {lat:40.413,lng:-3.698,name:'Nala',breed:'Border Collie',compat:78},
        {lat:40.418,lng:-3.716,name:'Bruno',breed:'Pastor Alemán',compat:82},
    ];

    perros.forEach(d => {
        L.marker([d.lat, d.lng], {icon: makeIcon(d.compat)})
         .addTo(map)
         .bindPopup(`<strong>${d.name}</strong><br><span style="color:#737373">${d.breed}</span><br><strong style="color:#f02d5e">${d.compat}% compatible</strong>`);
    });

    L.circleMarker([40.4168, -3.7038], {
        radius: 10, color: '#f02d5e', fillColor: '#f02d5e', fillOpacity: 0.2, weight: 2
    }).addTo(map).bindPopup('📍 Tu ubicación');
}
</script>
