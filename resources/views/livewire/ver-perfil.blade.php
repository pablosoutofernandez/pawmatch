<div class="w-full flex items-start paw-canvas min-h-screen">
    @include('partials.sidebar')

    <div class="flex-1 min-w-0 min-h-screen pb-20">

        {{-- ═══════════════════════ Flash ═══════════════════════ --}}
        @if(session('success'))
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-10 pt-6">
                <div class="flex items-center gap-3 bg-sage-50 ring-1 ring-sage-200 text-sage-700 px-5 py-3.5 rounded-2xl shadow-soft animate-fade-in-down">
                    <span class="text-lg">🎉</span>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if(session('premium'))
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-10 pt-6">
                <div class="flex items-center justify-between gap-3 bg-brand-50 ring-1 ring-brand-100 text-brand-700 px-5 py-3.5 rounded-2xl shadow-soft animate-fade-in-down">
                    <span class="text-sm font-semibold">⭐ {{ session('premium') }}</span>
                    <a href="{{ route('premium') }}" class="shrink-0 px-4 py-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold transition">Hazte Premium</a>
                </div>
            </div>
        @endif

        @php
            $compatNivel = match(true) {
                $compatibilidad === null      => null,
                $compatibilidad >= 85         => ['t' => 'Pareja ideal',       'd' => 'Seguro que se llevarían genial', 'c' => 'sage'],
                $compatibilidad >= 70         => ['t' => 'Buena conexión',      'd' => 'Tienen mucho en común',          'c' => 'sage'],
                $compatibilidad >= 55         => ['t' => 'Compatibilidad media','d' => 'Podría funcionar con paciencia', 'c' => 'brand'],
                default                       => ['t' => 'Polos opuestos',      'd' => 'Personalidades muy distintas',   'c' => 'brand'],
            };
            $energiaIconos = ['', '😴', '🚶', '🐕', '⚡', '🚀'];
        @endphp

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-10 pt-6 sm:pt-10">

            {{-- ══════════ BARRA SUPERIOR ══════════ --}}
            <div class="flex items-center justify-between gap-3 mb-6 animate-fade-up">
                @if($modoPerro)
                    <a href="{{ route('discover') }}"
                       class="inline-flex items-center gap-2 text-sm font-semibold text-ink-700/55 hover:text-brand-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Volver a explorar
                    </a>
                @else
                    <a href="{{ route('discover') }}"
                       class="inline-flex items-center gap-2 text-sm font-semibold text-ink-700/55 hover:text-brand-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                        {{ $esMiPerfil ? 'Volver a explorar' : 'Atrás' }}
                    </a>
                @endif

                <div class="flex items-center gap-2">
                    @if($esMiPerfil)
                        <a href="{{ route('perfil') }}" class="btn-soft text-sm py-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                            </svg>
                            Editar perfil
                        </a>
                        <a href="{{ route('perfil', ['paso' => 3]) }}"
                           class="btn-soft text-sm py-2 {{ $perfil->tiene_ubicacion ? '' : 'ring-2 ring-brand-400 text-brand-700 bg-brand-50 hover:bg-brand-100' }}"
                           title="{{ $perfil->tiene_ubicacion ? 'Cambiar tu ubicación' : 'Configura tu ubicación' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                            </svg>
                            {{ $perfil->tiene_ubicacion ? 'Ubicación' : 'Configurar ubicación' }}
                        </a>
                    @elseif($esMatch && $conversacionId)
                        <a href="{{ route('chat') }}" class="btn-sage text-sm py-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.556-4.03 8.25-9 8.25a9.76 9.76 0 0 1-2.555-.337A5.97 5.97 0 0 1 5.41 20.97a4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/>
                            </svg>
                            Escribir mensaje
                        </a>
                    @endif
                </div>
            </div>

            {{-- Banner: invita a fijar la ubicación si todavía no la tiene --}}
            @if($esMiPerfil && !$perfil->tiene_ubicacion)
                <a href="{{ route('perfil', ['paso' => 3]) }}"
                   class="block soft-card p-5 ring-2 ring-brand-300 bg-gradient-to-r from-brand-50 to-cream-50 hover:from-brand-100 hover:to-cream-100 transition-all animate-pop-in">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-brand-500 flex items-center justify-center text-white text-2xl">📍</div>
                        <div class="flex-1 min-w-0">
                            <p class="h-display text-lg text-ink-800 leading-tight">Aún no has fijado tu ubicación</p>
                            <p class="text-sm text-ink-700/60 mt-0.5">Configúrala para ver perros cerca de ti, aparecer en el mapa y calcular distancias reales.</p>
                        </div>
                        <span class="text-brand-600 font-bold text-sm flex-shrink-0">Configurar →</span>
                    </div>
                </a>
            @endif

            {{-- Ajuste: ubicación en tiempo real (solo en mi perfil) --}}
            @if($esMiPerfil)
                <div class="soft-card p-5 flex items-start gap-4 animate-pop-in mt-4
                            {{ $perfil->ubicacion_tiempo_real ? 'ring-2 ring-brand-200' : '' }}">
                    <button type="button" wire:click="toggleUbicacionTiempoReal"
                            class="flex-shrink-0 mt-0.5 w-11 h-6 rounded-full relative transition-colors {{ $perfil->ubicacion_tiempo_real ? 'bg-brand-500' : 'bg-cream-300' }}"
                            aria-label="Activar o desactivar ubicación en tiempo real">
                        <span class="w-5 h-5 bg-white rounded-full absolute top-0.5 shadow-sm transition-all {{ $perfil->ubicacion_tiempo_real ? 'left-5' : 'left-0.5' }}"></span>
                    </button>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-ink-800 flex items-center gap-2">
                            📍 Ubicación en tiempo real
                            @if($perfil->ubicacion_tiempo_real)
                                <span class="pill pill-pink text-[11px]">Activada</span>
                            @endif
                        </p>
                        <p class="text-xs text-ink-700/55 mt-0.5 leading-relaxed">
                            @if($perfil->ubicacion_tiempo_real)
                                Tu posición se actualiza automáticamente mientras usas la app. Otros usuarios te verán moverte en el mapa.
                            @else
                                Tu ubicación queda fija en el último punto guardado. Actívala para actualizarla en directo.
                            @endif
                            @unless($perfil->tiene_ubicacion)
                                <span class="block mt-1 text-brand-600 font-semibold">Necesitas fijar tu ubicación primero (botón "Configurar ubicación").</span>
                            @endunless
                        </p>
                    </div>
                </div>
            @endif

            @if($perro)
                {{-- ══════════ HÉROE — perro destacado ══════════ --}}
                <div class="soft-card overflow-hidden animate-pop-in">
                    <div class="grid md:grid-cols-[minmax(0,1fr)_1.1fr]">

                        {{-- Foto del perro --}}
                        <div class="relative aspect-square md:aspect-auto md:min-h-[26rem] bg-gradient-to-br from-brand-100 via-cream-200 to-sage-100">
                            <img src="{{ $perro->foto_url }}"
                                 class="absolute inset-0 w-full h-full object-cover"
                                 alt="{{ $perro->nombre }}" onerror="this.onerror=null; this.src='{{ $perro->placeholder_url }}'">

                            <div class="absolute top-4 left-4 flex flex-col gap-2">
                                <span class="inline-flex items-center gap-1.5 bg-white/85 backdrop-blur-sm text-ink-700
                                             text-[11px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-full shadow-soft">
                                    🐾 PawMatch
                                </span>
                            </div>

                            @if(count($galeria) > 0)
                                <div class="absolute bottom-4 left-4 flex gap-2">
                                    @foreach(array_slice($galeria, 0, 3) as $foto)
                                        <div class="w-12 h-12 rounded-xl overflow-hidden ring-2 ring-white/80 shadow-lift">
                                            <img src="{{ $foto }}" class="w-full h-full object-cover" alt="">
                                        </div>
                                    @endforeach
                                    @if(count($galeria) > 3)
                                        <div class="w-12 h-12 rounded-xl bg-ink-900/55 backdrop-blur-sm ring-2 ring-white/80 flex items-center justify-center text-white text-xs font-bold">
                                            +{{ count($galeria) - 3 }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Datos del perro --}}
                        <div class="p-6 sm:p-8 flex flex-col">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h1 class="h-display text-4xl sm:text-5xl text-ink-900 leading-none">{{ $perro->nombre }}</h1>
                                    <p class="text-ink-700/55 mt-2 text-[15px] font-medium">
                                        {{ $perro->raza ?: 'Raza mestiza' }}
                                    </p>
                                </div>
                                @if($perro->sexo)
                                    <span class="flex-shrink-0 inline-flex items-center gap-1.5 text-sm font-semibold px-3 py-1.5 rounded-full
                                         {{ $perro->sexo === 'macho' ? 'bg-sage-100 text-sage-700' : 'bg-brand-100 text-brand-700' }}">
                                        {{ $perro->sexo === 'macho' ? '♂' : '♀' }}
                                        {{ ucfirst($perro->sexo) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Datos clave --}}
                            <div class="grid grid-cols-3 gap-3 mt-6">
                                <div class="bg-cream-50 rounded-2xl px-3 py-3.5 text-center ring-1 ring-cream-300">
                                    @php
                                        $anios = $perro->edad_anios ?? 0;
                                        $meses = $perro->edad_meses ?? 0;
                                        $totalMeses = $anios * 12 + $meses;
                                    @endphp
                                    @if($anios > 0 && $meses > 0)
                                        <p class="h-display text-xl text-ink-900 leading-none">{{ $anios }}<span class="text-sm">a</span> {{ $meses }}<span class="text-sm">m</span></p>
                                        <p class="text-[10px] text-ink-700/50 font-semibold mt-1 uppercase tracking-wide">{{ $totalMeses }} meses</p>
                                    @elseif($anios > 0)
                                        <p class="h-display text-2xl text-ink-900 leading-none">{{ $anios }}</p>
                                        <p class="text-[11px] text-ink-700/50 font-semibold mt-1 uppercase tracking-wide">{{ $anios === 1 ? 'año' : 'años' }}</p>
                                    @else
                                        <p class="h-display text-2xl text-ink-900 leading-none">{{ $meses }}</p>
                                        <p class="text-[11px] text-ink-700/50 font-semibold mt-1 uppercase tracking-wide">meses</p>
                                    @endif
                                </div>
                                <div class="bg-cream-50 rounded-2xl px-3 py-3.5 text-center ring-1 ring-cream-300">
                                    <p class="h-display text-2xl text-ink-900 leading-none">{{ $perro->peso_kg ? rtrim(rtrim(number_format($perro->peso_kg,1),'0'),'.') : '—' }}</p>
                                    <p class="text-[11px] text-ink-700/50 font-semibold mt-1 uppercase tracking-wide">kg</p>
                                </div>
                                <div class="bg-cream-50 rounded-2xl px-3 py-3.5 text-center ring-1 ring-cream-300">
                                    <p class="text-2xl leading-none">{{ $energiaIconos[$perro->energia] ?? '🐕' }}</p>
                                    <p class="text-[11px] text-ink-700/50 font-semibold mt-1.5 uppercase tracking-wide">{{ $perro->energia_texto }}</p>
                                </div>
                            </div>

                            {{-- Insignias de salud --}}
                            <div class="flex flex-wrap gap-2 mt-4">
                                @if($perro->peso_kg)
                                    <span class="pill pill-cream">📏 {{ $perro->tamano }}</span>
                                @endif
                                @if($perro->vacunado)
                                    <span class="pill pill-sage">💉 Vacunado/a</span>
                                @endif
                                @if($perro->esterilizado)
                                    <span class="pill pill-sage">✓ Esterilizado/a</span>
                                @endif
                            </div>

                            {{-- Carácter --}}
                            @if($perro->caracter && count($perro->caracter))
                                <div class="mt-5">
                                    <p class="text-[11px] text-ink-700/45 font-bold uppercase tracking-wider mb-2">Personalidad</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($perro->caracter as $rasgo)
                                            <span class="pill pill-pink capitalize">{{ $rasgo }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Like al perro destacado (perfiles ajenos) --}}
                            @if(!$esMiPerfil)
                                <div class="mt-6">
                                    @if($this->esMatchPerro($perro->id))
                                        <span class="inline-flex items-center gap-2 font-semibold text-sm px-5 py-2.5 rounded-full bg-brand-100 text-brand-600">💞 ¡Match con {{ $perro->nombre }}!</span>
                                    @elseif($this->yaLikePerro($perro->id))
                                        <span class="inline-flex items-center gap-2 font-semibold text-sm px-5 py-2.5 rounded-full bg-cream-200 text-ink-700/50">✓ Like enviado</span>
                                    @else
                                        <button wire:click="darLikePerro({{ $perro->id }})"
                                                class="inline-flex items-center gap-2 font-semibold text-sm px-5 py-2.5 rounded-full shadow-soft transition-all active:scale-[.97] bg-gradient-to-br from-brand-400 to-brand-600 text-white hover:from-brand-500 hover:to-brand-700">
                                            <span wire:loading.remove wire:target="darLikePerro({{ $perro->id }})">♥ Me gusta {{ $perro->nombre }}</span>
                                            <span wire:loading wire:target="darLikePerro({{ $perro->id }})" class="flex items-center gap-1.5">
                                                <span class="w-3.5 h-3.5 rounded-full border-2 border-white border-t-transparent animate-spin"></span>Enviando
                                            </span>
                                        </button>
                                    @endif
                                </div>
                            @endif

                            {{-- Dueño --}}
                            <div class="mt-auto pt-6">
                                <a href="{{ route('ver-perfil', $perfil->id) }}" class="flex items-center gap-3 border-t border-cream-200 pt-4 hover:opacity-80 transition-opacity">
                                    <div class="w-11 h-11 rounded-full overflow-hidden ring-2 ring-cream-200 bg-gradient-to-br from-brand-200 to-sage-200 flex items-center justify-center flex-shrink-0">
                                        @if($perfil->avatar_photo)
                                            <img src="{{ $perfil->avatar_photo }}" class="w-full h-full object-cover" alt="{{ $perfil->name }}">
                                        @else
                                            <span class="text-white font-bold">{{ strtoupper(substr($perfil->name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] text-ink-700/45 font-semibold">Su humano · ver perfil →</p>
                                        <p class="text-sm font-bold text-ink-800 flex items-center gap-1.5 truncate">
                                            {{ $perfil->name }}
                                            @if($perfil->es_premium)<x-premium-badge />@endif
                                        </p>
                                    </div>
                                    @if($perfil->paseando_ahora)
                                        <span class="ml-auto pill pill-sage flex items-center gap-1.5 flex-shrink-0">
                                            <span class="w-1.5 h-1.5 rounded-full bg-sage-500 animate-pulse"></span>
                                            Paseando
                                        </span>
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════ COMPATIBILIDAD ══════════ --}}
                @if(!$esMiPerfil && $compatibilidad !== null && $compatNivel)
                    <div class="soft-card p-6 sm:p-7 mt-6 animate-fade-up overflow-hidden relative">
                        <div class="flex items-center gap-5 sm:gap-7 flex-wrap">
                            <div class="relative w-28 h-28 flex-shrink-0">
                                <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                                    <circle cx="50" cy="50" r="42" fill="none" stroke="#f1ebdf" stroke-width="9"/>
                                    <circle cx="50" cy="50" r="42" fill="none"
                                            stroke="{{ $compatNivel['c'] === 'sage' ? '#5f7d53' : '#cf5f80' }}"
                                            stroke-width="9" stroke-linecap="round"
                                            stroke-dasharray="{{ 2 * 3.14159 * 42 }}"
                                            stroke-dashoffset="{{ 2 * 3.14159 * 42 * (1 - $compatibilidad / 100) }}"/>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="h-display text-3xl {{ $compatNivel['c'] === 'sage' ? 'text-sage-600' : 'text-brand-500' }}">{{ $compatibilidad }}%</span>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] text-ink-700/45 font-bold uppercase tracking-wider">Compatibilidad de {{ $perro->nombre }} con tu perro</p>
                                <h3 class="h-display text-2xl text-ink-900 mt-1">{{ $compatNivel['t'] }}</h3>
                                <p class="text-sm text-ink-700/60 mt-0.5">{{ $compatNivel['d'] }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ══════════ OTROS PERROS DEL USUARIO (sólo modo usuario) ══════════ --}}
                @if(!$modoPerro && $perros->count() > 1)
                    <div class="soft-card p-6 mt-6 animate-fade-up">
                        <h3 class="h-display text-lg text-ink-900 mb-4">
                            {{ $esMiPerfil ? 'Mis perros' : 'Los perros de '.explode(' ', $perfil->name)[0] }}
                            <span class="text-sm text-ink-700/45 font-sans">({{ $perros->count() }})</span>
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($perros as $p)
                                <div class="flex items-center gap-4 p-3 rounded-2xl ring-1 ring-cream-200 bg-cream-50/50 hover:shadow-soft transition-all">
                                    <a href="{{ route('ver-perro', $p->id) }}" class="flex items-center gap-4 flex-1 min-w-0">
                                        <div class="w-16 h-16 rounded-2xl overflow-hidden bg-gradient-to-br from-brand-100 to-sage-100 flex items-center justify-center flex-shrink-0">
                                            <img src="{{ $p->foto_url }}" class="w-full h-full object-cover" alt="{{ $p->nombre }}" onerror="this.onerror=null; this.src='{{ $p->placeholder_url }}'">
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-ink-900 truncate">{{ $p->nombre }}</p>
                                            <p class="text-xs text-ink-700/55 truncate">{{ $p->raza ?: 'Mestizo' }} · {{ $p->edad_texto }}</p>
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @if($p->peso_kg)<span class="pill pill-cream text-[10px]">{{ $p->tamano }}</span>@endif
                                                <span class="pill pill-pink text-[10px]">⚡ {{ $p->energia_texto }}</span>
                                            </div>
                                        </div>
                                    </a>
                                    @if(!$esMiPerfil)
                                        <div class="flex-shrink-0">
                                            @if($this->esMatchPerro($p->id))
                                                <span class="pill pill-pink text-[11px]">💞 Match</span>
                                            @elseif($this->yaLikePerro($p->id))
                                                <span class="pill pill-cream text-[11px]">✓ Like</span>
                                            @else
                                                <button wire:click="darLikePerro({{ $p->id }})" class="btn-primary !py-2 !px-3 text-xs">
                                                    <span wire:loading.remove wire:target="darLikePerro({{ $p->id }})">♥</span>
                                                    <span wire:loading wire:target="darLikePerro({{ $p->id }})">…</span>
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ══════════ DETALLE ══════════ --}}
                <div class="grid lg:grid-cols-3 gap-6 mt-6">

                    <div class="lg:col-span-2 space-y-6">

                        @if($perro->descripcion)
                            <div class="soft-card p-6 animate-fade-up">
                                <h3 class="h-display text-lg text-ink-900 mb-2 flex items-center gap-2">
                                    <span class="text-brand-400">🐾</span> Sobre {{ $perro->nombre }}
                                </h3>
                                <p class="text-[15px] text-ink-700/80 leading-relaxed">{{ $perro->descripcion }}</p>
                            </div>
                        @endif

                        <div class="soft-card p-6 animate-fade-up">
                            <h3 class="h-display text-lg text-ink-900 mb-5">Ficha completa</h3>

                            <div class="mb-5">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-semibold text-ink-700/70">Nivel de energía</span>
                                    <span class="text-sm font-semibold text-brand-500">{{ $energiaIconos[$perro->energia] ?? '' }} {{ $perro->energia_texto }}</span>
                                </div>
                                <div class="flex gap-1.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <div class="flex-1 h-2.5 rounded-full transition-all {{ $i <= $perro->energia ? 'bg-gradient-to-r from-brand-300 to-brand-500' : 'bg-cream-200' }}"></div>
                                    @endfor
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-x-8 gap-y-px">
                                @php
                                    $filas = [
                                        ['Tamaño',  $perro->peso_kg ? $perro->tamano : null],
                                        ['Peso',    $perro->peso_kg ? rtrim(rtrim(number_format($perro->peso_kg,1),'0'),'.').' kg' : null],
                                        ['Edad',    $perro->edad_texto],
                                        ['Sexo',    $perro->sexo ? ucfirst($perro->sexo) : null],
                                    ];
                                @endphp
                                @foreach($filas as [$k,$v])
                                    @if($v)
                                        <div class="flex items-center justify-between py-2.5 border-b border-cream-200/70">
                                            <span class="text-sm text-ink-700/55">{{ $k }}</span>
                                            <span class="text-sm font-semibold text-ink-800">{{ $v }}</span>
                                        </div>
                                    @endif
                                @endforeach

                                @php
                                    $bools = [
                                        ['Vacunado/a',     $perro->vacunado],
                                        ['Esterilizado/a', $perro->esterilizado],
                                    ];
                                @endphp
                                @foreach($bools as [$k,$v])
                                    <div class="flex items-center justify-between py-2.5 border-b border-cream-200/70">
                                        <span class="text-sm text-ink-700/55">{{ $k }}</span>
                                        <span class="text-sm font-semibold inline-flex items-center gap-1 {{ $v ? 'text-sage-600' : 'text-ink-700/35' }}">
                                            {{ $v ? '✓ Sí' : '— No' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if(count($galeria) > 0)
                            <div class="soft-card p-6 animate-fade-up">
                                <h3 class="h-display text-lg text-ink-900 mb-4">Más fotos</h3>
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                    @foreach($galeria as $foto)
                                        <div class="aspect-square rounded-2xl overflow-hidden bg-cream-200 ring-1 ring-cream-300 hover:scale-[1.03] transition-transform">
                                            <img src="{{ $foto }}" class="w-full h-full object-cover" alt="">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($esMiPerfil)
                            <div class="soft-card-flat p-5 flex items-center justify-between flex-wrap gap-3 animate-fade-up">
                                <div>
                                    <p class="font-bold text-ink-800 text-sm">¿Mejoramos los perfiles de tus perros?</p>
                                    <p class="text-xs text-ink-700/50 mt-0.5">Añade fotos, ajusta descripciones o registra otro perro</p>
                                </div>
                                <a href="{{ route('perfil') }}?paso=2" class="btn-soft text-sm py-2">Gestionar perros →</a>
                            </div>
                        @endif
                    </div>

                    {{-- Barra lateral --}}
                    <div class="space-y-6">

                        <div class="soft-card p-6 animate-fade-up">
                            <a href="{{ route('ver-perfil', $perfil->id) }}" class="flex flex-col items-center text-center hover:opacity-90 transition-opacity">
                                <div class="w-20 h-20 rounded-full overflow-hidden ring-4 ring-cream-100 shadow-soft bg-gradient-to-br from-brand-200 to-sage-200 flex items-center justify-center">
                                    @if($perfil->avatar_photo)
                                        <img src="{{ $perfil->avatar_photo }}" class="w-full h-full object-cover" alt="{{ $perfil->name }}">
                                    @else
                                        <span class="h-display text-3xl text-white">{{ strtoupper(substr($perfil->name, 0, 1)) }}</span>
                                    @endif
                                </div>
                                <h3 class="h-display text-xl text-ink-900 mt-3 flex items-center gap-2">
                                    {{ $perfil->name }}
                                    @if($perfil->es_premium)<x-premium-badge size="md" />@endif
                                </h3>
                                @if($perfil->ciudad)
                                    <p class="text-sm text-ink-700/55 mt-0.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                                        </svg>
                                        {{ $perfil->ciudad }}
                                    </p>
                                @endif
                            </a>

                            @if($perfil->bio)
                                <p class="text-sm text-ink-700/75 leading-relaxed mt-4 pt-4 border-t border-cream-200">{{ $perfil->bio }}</p>
                            @endif

                            <div class="mt-4 pt-4 border-t border-cream-200 flex items-center justify-center gap-2 text-xs text-ink-700/55">
                                🐾 {{ $perfil->perros->count() }} {{ $perfil->perros->count() === 1 ? 'perro' : 'perros' }}
                            </div>
                        </div>

                        <div class="soft-card p-6 animate-fade-up">
                            <h3 class="h-display text-base text-ink-900 mb-4">Actividad</h3>
                            <div class="grid grid-cols-3 gap-3">
                                @php
                                    $stats = [
                                        ['💞', $totalMatches,   'Matches'],
                                        ['♥',  $likesRecibidos, 'Likes'],
                                        ['📅', $diasEnPawMatch,  $diasEnPawMatch === 1 ? 'Día aquí' : 'Días aquí'],
                                    ];
                                @endphp
                                @foreach($stats as [$ico,$val,$lab])
                                    <div class="bg-cream-50 rounded-2xl p-3.5 ring-1 ring-cream-200">
                                        <p class="text-lg">{{ $ico }}</p>
                                        <p class="h-display text-2xl text-ink-900 leading-none mt-1">{{ $val }}</p>
                                        <p class="text-[11px] text-ink-700/50 font-semibold mt-0.5">{{ $lab }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($esMiPerfil)
                            <div class="soft-card p-6 animate-fade-up">
                                <h3 class="h-display text-base text-ink-900 mb-3">Mi plan</h3>
                                <div class="flex items-center justify-between">
                                    <span class="pill {{ $perfil->plan === 'premium' ? 'pill-pink' : 'pill-cream' }} text-sm">
                                        {{ $perfil->plan === 'premium' ? '✨ Premium' : '🐾 Gratuito' }}
                                    </span>
                                    @if($perfil->plan !== 'premium')
                                        <a href="#" class="text-xs font-bold text-brand-500 hover:text-brand-600">Mejorar →</a>
                                    @endif
                                </div>
                                @if($perfil->plan === 'premium' && $perfil->plan_expira_at)
                                    <p class="text-xs text-ink-700/45 mt-2">Activo hasta {{ $perfil->plan_expira_at->format('d/m/Y') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

            @else
                {{-- ══════════ SIN PERRO ══════════ --}}
                <div class="soft-card p-12 text-center max-w-xl mx-auto mt-6 animate-pop-in">
                    <div class="text-7xl mb-4 animate-float-slow inline-block">🐶</div>
                    <h3 class="h-display text-2xl text-ink-900">
                        @if($esMiPerfil) Aún no has presentado a tu perro
                        @else Este usuario todavía no tiene perro @endif
                    </h3>
                    <p class="text-sm text-ink-700/55 mt-2 mb-6 max-w-sm mx-auto">
                        @if($esMiPerfil) El perfil de tu perro es lo primero que ven los demás. ¡Es la mejor carta de presentación!
                        @else Puede que lo añada pronto. @endif
                    </p>
                    @if($esMiPerfil)
                        <a href="{{ route('perfil') }}" class="btn-primary">Añadir mi perro</a>
                    @endif
                </div>
            @endif

        </div>
    </div>
</div>
