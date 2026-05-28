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

        <div class="w-full max-w-5xl mx-auto space-y-7">

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-3 animate-fade-up">
                <div>
                    <p class="pill pill-pink mb-3">🔍 Explora la manada</p>
                    <h1 class="h-display text-4xl sm:text-5xl leading-none">Descubrir</h1>
                    <p class="text-ink-700/60 mt-2 text-[15px]">Perros compatibles paseando cerca de ti.</p>
                </div>
                <span class="pill pill-cream">{{ $perros->total() }} resultados</span>
            </div>

            {{-- Filtros --}}
            <div class="soft-card-flat p-5 animate-fade-up" style="animation-delay:60ms">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="field-label">Nombre</label>
                        <input type="text" wire:model.live.debounce.400ms="f_nombre" placeholder="Ej: Rocky" class="field">
                    </div>
                    <div>
                        <label class="field-label">Raza</label>
                        <input type="text" wire:model.live.debounce.400ms="f_raza" placeholder="Ej: Golden..." class="field">
                    </div>
                    <div>
                        <label class="field-label">Energía mínima</label>
                        <select wire:model.live="f_energia_min" class="field">
                            <option value="">Cualquiera</option>
                            <option value="1">Muy baja</option>
                            <option value="2">Baja</option>
                            <option value="3">Media</option>
                            <option value="4">Alta</option>
                            <option value="5">Muy alta</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Disponibilidad</label>
                        <label class="flex items-center gap-2.5 cursor-pointer px-4 py-3 bg-cream-50 ring-1 ring-cream-300 rounded-2xl">
                            <input type="checkbox" wire:model.live="solo_disponibles" class="rounded-md text-brand-500 focus:ring-brand-400 border-cream-300">
                            <span class="text-sm text-ink-700">Paseando ahora 🌿</span>
                        </label>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-[13px] font-semibold text-ink-700/55 mr-1">Tamaño:</span>
                    @foreach(['todos' => 'Todos', 'pequeno' => 'Pequeño', 'mediano' => 'Mediano', 'grande' => 'Grande'] as $valor => $label)
                    <button wire:click="setTamano('{{ $valor }}')"
                            class="px-4 py-1.5 rounded-full text-sm font-semibold transition-all
                                   {{ $f_tamano === $valor
                                        ? 'bg-brand-500 text-white shadow-soft'
                                        : 'bg-cream-100 text-ink-700/70 ring-1 ring-cream-300 hover:ring-brand-200' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                    <button wire:click="limpiarFiltros" class="ml-auto text-sm font-semibold text-ink-700/50 hover:text-brand-600 transition-colors">
                        Limpiar
                    </button>
                </div>
            </div>

            {{-- Grid de perros — 4 a la vez, tarjetas más grandes y expresivas --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" wire:loading.class="opacity-60">

                @forelse($perros as $i => $perro)
                <div class="soft-card overflow-hidden hover:shadow-lift hover:-translate-y-1 transition-all duration-300 animate-pop-in"
                     style="animation-delay: {{ $i * 60 }}ms">

                    {{-- Foto principal --}}
                    <div class="relative aspect-[16/9] bg-gradient-to-br from-brand-100 via-cream-200 to-sage-100 overflow-hidden">

                        @if($perro->foto_principal)
                        <img src="{{ $perro->foto_principal }}"
                             alt="{{ $perro->nombre }}"
                             class="w-full h-full object-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                        <div class="hidden absolute inset-0 items-center justify-center text-7xl">🐶</div>
                        @else
                        <div class="absolute inset-0 flex items-center justify-center text-7xl">🐶</div>
                        @endif

                        {{-- Badge compatibilidad --}}
                        <div class="absolute top-3 right-3 bg-white/95 backdrop-blur rounded-full px-3 py-1.5 flex items-center gap-1.5 shadow-soft">
                            <div class="w-2 h-2 rounded-full
                                {{ $perro->compatibilidad >= 85 ? 'bg-sage-500' : ($perro->compatibilidad >= 70 ? 'bg-brand-400' : 'bg-cream-300') }}">
                            </div>
                            <span class="text-xs font-bold text-ink-800">{{ $perro->compatibilidad }}%</span>
                        </div>

                        {{-- Badge paseando --}}
                        @if($perro->disponible_ahora)
                        <div class="absolute top-3 left-3 flex items-center gap-1.5 bg-sage-500 text-white text-[11px] font-bold px-2.5 py-1 rounded-full shadow-soft">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                            Paseando ahora
                        </div>
                        @endif

                        {{-- Chip dueño — enlaza a su perfil --}}
                        <a href="{{ route('ver-perfil', $perro->dueno->id) }}"
                           class="absolute bottom-3 left-3 flex items-center gap-2 bg-white/85 backdrop-blur-sm rounded-full pl-1 pr-3 py-1 shadow-soft hover:bg-white transition-colors">
                            @if($perro->dueno->avatar_photo)
                            <img src="{{ $perro->dueno->avatar_photo }}"
                                 alt="{{ $perro->dueno->name }}"
                                 class="w-7 h-7 rounded-full object-cover ring-2 ring-white"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                            <span class="hidden w-7 h-7 rounded-full bg-gradient-to-br from-brand-300 to-brand-500 items-center justify-center text-[11px] font-bold text-white ring-2 ring-white">
                                {{ strtoupper(substr($perro->dueno->name, 0, 1)) }}
                            </span>
                            @else
                            <span class="w-7 h-7 rounded-full bg-gradient-to-br from-brand-300 to-brand-500 flex items-center justify-center text-[11px] font-bold text-white ring-2 ring-white">
                                {{ strtoupper(substr($perro->dueno->name, 0, 1)) }}
                            </span>
                            @endif
                            <span class="text-xs font-semibold text-ink-800">{{ explode(' ', $perro->dueno->name)[0] }}</span>
                        </a>
                    </div>

                    {{-- Info --}}
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <h3 class="h-display text-2xl text-ink-900 leading-tight">{{ $perro->nombre }}</h3>
                            <span class="text-[12px] font-semibold text-ink-700/40 flex-shrink-0 mt-1.5">{{ $perro->distancia }}</span>
                        </div>

                        <p class="text-sm text-ink-700/55 mb-3">
                            {{ $perro->raza }} · {{ $perro->edad_texto }}
                            @if($perro->sexo) · {{ $perro->sexo === 'macho' ? '♂' : '♀' }} @endif
                        </p>

                        {{-- Descripción --}}
                        @if($perro->descripcion)
                        <p class="text-[14px] text-ink-700/75 leading-relaxed mb-4 line-clamp-2">
                            {{ $perro->descripcion }}
                        </p>
                        @endif

                        {{-- Pills --}}
                        <div class="flex flex-wrap gap-1.5 mb-4">
                            <span class="pill pill-cream text-[11px]">{{ $perro->tamano }}</span>
                            <span class="pill pill-pink text-[11px]">⚡ {{ $perro->energia_texto }}</span>
                            @if($perro->vacunado)<span class="pill pill-sage text-[11px]">✓ Vacunado</span>@endif
                            @foreach(array_slice($perro->caracter ?? [], 0, 2) as $rasgo)
                            <span class="pill pill-cream text-[11px] capitalize">{{ $rasgo }}</span>
                            @endforeach
                        </div>

                        {{-- Barra de compatibilidad --}}
                        <div class="compat-bar mb-1.5">
                            <div class="compat-fill" style="width:{{ $perro->compatibilidad }}%"></div>
                        </div>
                        <div class="flex justify-between mb-4">
                            <span class="text-[11px] text-ink-700/40">Compatibilidad</span>
                            <span class="text-[11px] font-bold text-brand-500">{{ $perro->compatibilidad }}%</span>
                        </div>

                        {{-- Acciones --}}
                        <div class="grid grid-cols-2 gap-2.5">
                            <button wire:click="pasar({{ $perro->id }})"
                                    @if(isset($likesDados[$perro->id])) disabled @endif
                                    class="btn-soft !py-2.5 text-sm disabled:opacity-50">
                                Pasar 👋
                            </button>
                            <button wire:click="darLike({{ $perro->id }})"
                                    @if(isset($likesDados[$perro->id]) && $likesDados[$perro->id]) disabled @endif
                                    class="btn-primary !py-2.5 text-sm disabled:opacity-60">
                                <span wire:loading.remove wire:target="darLike({{ $perro->id }})">
                                    @if(isset($likesDados[$perro->id]) && $likesDados[$perro->id])
                                        ✓ ¡Match!
                                    @else
                                        ♥ Me gusta
                                    @endif
                                </span>
                                <span wire:loading wire:target="darLike({{ $perro->id }})">...</span>
                            </button>
                        </div>
                    </div>
                </div>

                @empty
                <div class="col-span-full text-center py-20 soft-card-flat">
                    <div class="text-6xl mb-3 animate-float-slow inline-block">🔍</div>
                    <p class="h-display text-xl text-ink-800">Sin resultados</p>
                    <p class="text-sm text-ink-700/55 mt-1">Prueba con otros filtros o amplía la búsqueda</p>
                </div>
                @endforelse
            </div>

            {{-- Paginación --}}
            @if($perros->hasPages())
            <div class="soft-card-flat p-4">
                {{ $perros->links() }}
            </div>
            @endif

        </div>
    </div>
</div>
