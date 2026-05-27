<div class="w-full grid grid-cols-12 justify-start items-start gap-4">
    @include('partials.sidebar')

    <div class="w-full col-span-12 md:col-span-11 min-h-screen flex flex-col justify-start items-start pt-6 md:pt-10 px-4 sm:px-6 lg:px-8 bg-slate-50/50">

        {{-- Mensajes flash --}}
        @if(session('success'))
            <div class="w-full max-w-6xl mx-auto mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-600 px-6 py-4 rounded-2xl shadow-sm animate-fade-in-down">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        <div class="w-full max-w-6xl mx-auto space-y-6">

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Discover</h2>
                    <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold mt-1">
                        Perros compatibles cerca de ti
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-semibold">{{ $perros->total() }} resultados</span>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="bg-white rounded-2xl p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 ml-1">Nombre</label>
                        <input type="text" wire:model.live.debounce.400ms="f_nombre"
                               placeholder="Ej: Rocky"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 ml-1">Raza</label>
                        <input type="text" wire:model.live.debounce.400ms="f_raza"
                               placeholder="Ej: Golden..."
                               class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 ml-1">Energía mínima</label>
                        <select wire:model.live="f_energia_min"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            <option value="">Cualquiera</option>
                            <option value="1">Muy baja</option>
                            <option value="2">Baja</option>
                            <option value="3">Media</option>
                            <option value="4">Alta</option>
                            <option value="5">Muy alta</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 ml-1">Disponibilidad</label>
                        <label class="flex items-center gap-2 cursor-pointer mt-2.5">
                            <input type="checkbox" wire:model.live="solo_disponibles"
                                   class="rounded text-brand-500 focus:ring-brand-500">
                            <span class="text-sm text-slate-700">Solo paseando ahora 🟢</span>
                        </label>
                    </div>
                </div>

                {{-- Tamaño pills --}}
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mr-2">Tamaño:</span>
                    @foreach(['todos' => 'Todos', 'pequeno' => 'Pequeño', 'mediano' => 'Mediano', 'grande' => 'Grande'] as $valor => $label)
                    <button wire:click="setTamano('{{ $valor }}')"
                            class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border transition-all
                                   {{ $f_tamano === $valor
                                        ? 'bg-brand-500 text-white border-brand-500'
                                        : 'bg-white text-slate-600 border-slate-200 hover:border-brand-300' }}">
                        {{ $label }}
                    </button>
                    @endforeach

                    <button wire:click="limpiarFiltros"
                            class="ml-auto text-xs font-bold text-slate-500 hover:text-brand-600 transition-colors">
                        Limpiar filtros
                    </button>
                </div>
            </div>

            {{-- Grid de perros --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                 wire:loading.class="opacity-60">

                @forelse($perros as $perro)
                <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">

                    {{-- Foto --}}
                    <div class="relative aspect-[4/3] bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">
                        <span class="text-6xl opacity-40">🐶</span>

                        {{-- Compat badge --}}
                        <div class="absolute top-2.5 right-2.5 bg-white/95 rounded-full px-2.5 py-1 flex items-center gap-1.5 shadow-sm">
                            <div class="w-1.5 h-1.5 rounded-full {{ $perro->compatibilidad >= 85 ? 'bg-emerald-500' : ($perro->compatibilidad >= 70 ? 'bg-amber-500' : 'bg-slate-400') }}"></div>
                            <span class="text-xs font-bold text-slate-800">{{ $perro->compatibilidad }}%</span>
                        </div>

                        @if($perro->disponible_ahora)
                        <div class="absolute top-2.5 left-2.5 flex items-center gap-1 bg-emerald-500 text-white text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                            Disponible
                        </div>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="p-4">
                        <div class="flex items-baseline justify-between gap-1 mb-0.5">
                            <h3 class="font-bold text-slate-800 text-lg leading-tight">{{ $perro->nombre }}</h3>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex-shrink-0">{{ $perro->distancia }}</span>
                        </div>
                        <p class="text-sm text-slate-500 mb-2">{{ $perro->raza }} · {{ $perro->edad_texto }}</p>

                        <div class="flex gap-1.5 flex-wrap mb-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                {{ $perro->tamano }}
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                                ⚡ {{ $perro->energia_texto }}
                            </span>
                        </div>

                        <div class="compat-bar">
                            <div class="compat-fill" style="width:{{ $perro->compatibilidad }}%"></div>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-[10px] text-slate-400">Compatibilidad</span>
                            <span class="text-[10px] font-bold text-brand-600">{{ $perro->compatibilidad }}%</span>
                        </div>

                        {{-- Owner --}}
                        <div class="flex items-center gap-1.5 mt-3 pt-3 border-t border-slate-100">
                            <div class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-600">
                                {{ strtoupper(substr($perro->dueno->name, 0, 1)) }}
                            </div>
                            <span class="text-xs text-slate-500">{{ $perro->dueno->name }}</span>
                        </div>

                        {{-- Acciones --}}
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <button wire:click="pasar({{ $perro->id }})"
                                    @if(isset($likesDados[$perro->id])) disabled @endif
                                    class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-2 px-3 rounded-xl text-xs transition-all disabled:opacity-50">
                                Pasar
                            </button>
                            <button wire:click="darLike({{ $perro->id }})"
                                    @if(isset($likesDados[$perro->id]) && $likesDados[$perro->id]) disabled @endif
                                    class="bg-brand-500 hover:bg-brand-600 text-white font-bold py-2 px-3 rounded-xl text-xs shadow-lg shadow-brand-200 transition-all disabled:opacity-50">
                                <span wire:loading.remove wire:target="darLike({{ $perro->id }})">
                                    @if(isset($likesDados[$perro->id]) && $likesDados[$perro->id])
                                        ✓ Like
                                    @else
                                        ♥ Like
                                    @endif
                                </span>
                                <span wire:loading wire:target="darLike({{ $perro->id }})">...</span>
                            </button>
                        </div>
                    </div>
                </div>

                @empty
                <div class="col-span-full text-center py-16 text-slate-500 bg-white rounded-2xl border border-gray-100">
                    <div class="text-5xl mb-3">🔍</div>
                    <p class="font-bold">Sin resultados</p>
                    <p class="text-sm mt-1">Prueba con otros filtros o amplía el radio de búsqueda</p>
                </div>
                @endforelse
            </div>

            {{-- Paginación --}}
            @if($perros->hasPages())
            <div class="bg-white rounded-2xl p-4 shadow-[0_4px_20px_rgb(0,0,0,0.04)] border border-gray-100">
                {{ $perros->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
