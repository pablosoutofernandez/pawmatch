<div class="w-full flex items-start">
    @include('partials.sidebar')

    <div class="flex-1 min-w-0 min-h-screen pb-16">

        {{-- Flash --}}
        @if(session('success'))
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-10 pt-6">
            <div class="flex items-center gap-3 bg-sage-50 ring-1 ring-sage-200 text-sage-700 px-5 py-3.5 rounded-2xl shadow-soft animate-fade-in-down">
                <span class="text-lg">🎉</span>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════
             HERO — franja superior con foto de perro
             de fondo y gradiente
        ══════════════════════════════════════════ --}}
        <div class="relative h-56 sm:h-72 overflow-hidden bg-gradient-to-br from-brand-200 via-cream-200 to-sage-200">

            {{-- Foto del perro como fondo difuminado --}}
            @if($perro?->foto_principal)
            <img src="{{ $perro->foto_principal }}"
                 class="absolute inset-0 w-full h-full object-cover blur-sm scale-110 opacity-60"
                 alt="">
            @endif
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-white/20 to-white"></div>

            {{-- Botones de acción arriba a la derecha --}}
            <div class="absolute top-4 right-4 sm:right-8 flex items-center gap-2 z-10">
                @if($esMiPerfil)
                <a href="{{ route('perfil') }}"
                   class="flex items-center gap-2 bg-white/80 backdrop-blur-sm hover:bg-white
                          text-ink-700 font-semibold text-sm px-4 py-2 rounded-full shadow-soft transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                    </svg>
                    Editar perfil
                </a>
                @else
                    @if($esMatch && $conversacionId)
                    <a href="{{ route('chat') }}"
                       class="flex items-center gap-2 bg-sage-500 hover:bg-sage-600 text-white
                              font-semibold text-sm px-4 py-2 rounded-full shadow-soft transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/>
                        </svg>
                        Escribir mensaje
                    </a>
                    @endif

                    <button wire:click="darLike"
                            @if($yaLike) disabled @endif
                            class="flex items-center gap-2 font-semibold text-sm px-4 py-2 rounded-full shadow-soft transition-all
                                   {{ $esMatch
                                       ? 'bg-brand-100 text-brand-600 cursor-default'
                                       : ($yaLike
                                           ? 'bg-cream-200 text-ink-700/50 cursor-default'
                                           : 'bg-brand-500 hover:bg-brand-600 text-white') }}">
                        @if($esMatch)
                            💞 ¡Match!
                        @elseif($yaLike)
                            ✓ Like enviado
                        @else
                            <span wire:loading.remove wire:target="darLike">♥ Me gusta</span>
                            <span wire:loading wire:target="darLike" class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
                            </span>
                        @endif
                    </button>
                @endif
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             CONTENIDO
        ══════════════════════════════════════════ --}}
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-10 -mt-16 relative z-10 space-y-6">

            {{-- Cabecera del perfil: avatar flotando sobre el hero --}}
            <div class="flex items-end gap-5 flex-wrap">

                {{-- Avatar del dueño --}}
                <div class="w-28 h-28 rounded-[1.5rem] overflow-hidden ring-4 ring-white shadow-lift bg-gradient-to-br from-brand-200 to-sage-200 flex-shrink-0 flex items-center justify-center">
                    @if($perfil->avatar_photo)
                        <img src="{{ $perfil->avatar_photo }}" class="w-full h-full object-cover" alt="{{ $perfil->name }}">
                    @else
                        <span class="h-display text-4xl text-white">{{ strtoupper(substr($perfil->name, 0, 1)) }}</span>
                    @endif
                </div>

                <div class="pb-2 flex-1 min-w-0">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="h-display text-3xl sm:text-4xl text-ink-900 leading-tight">{{ $perfil->name }}</h1>
                        @if($perfil->es_premium)
                        <span class="pill pill-pink text-[11px]">✨ Premium</span>
                        @endif
                        @if($perfil->paseando_ahora)
                        <span class="pill pill-sage text-[11px] flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-sage-500 animate-pulse"></span>
                            Paseando ahora
                        </span>
                        @endif
                    </div>
                    @if($perfil->ciudad)
                    <p class="text-sm text-ink-700/55 mt-1 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                        </svg>
                        {{ $perfil->ciudad }}
                    </p>
                    @endif
                </div>
            </div>

            {{-- Grid principal: 2 columnas --}}
            <div class="grid lg:grid-cols-3 gap-6">

                {{-- Columna izquierda: info del dueño + stats --}}
                <div class="space-y-5">

                    {{-- Bio --}}
                    @if($perfil->bio)
                    <div class="soft-card p-5">
                        <h3 class="h-display text-base text-ink-700/60 mb-2">Sobre mí</h3>
                        <p class="text-[14px] text-ink-700/80 leading-relaxed">{{ $perfil->bio }}</p>
                    </div>
                    @endif

                    {{-- Stats --}}
                    <div class="soft-card p-5">
                        <h3 class="h-display text-base text-ink-700/60 mb-4">Actividad</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-ink-700/65 flex items-center gap-2">💞 Matches totales</span>
                                <span class="h-display text-xl text-ink-900">{{ $totalMatches }}</span>
                            </div>
                            <div class="h-px bg-cream-200"></div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-ink-700/65 flex items-center gap-2">♥ Likes recibidos</span>
                                <span class="h-display text-xl text-ink-900">{{ $likesRecibidos }}</span>
                            </div>
                            <div class="h-px bg-cream-200"></div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-ink-700/65 flex items-center gap-2">⭐ PawPoints</span>
                                <span class="h-display text-xl text-ink-900">{{ $perfil->puntos }}</span>
                            </div>
                            <div class="h-px bg-cream-200"></div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-ink-700/65">📅 Días en PawMatch</span>
                                <span class="h-display text-xl text-ink-900">{{ $diasEnPawMatch }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Compatibilidad (solo si es perfil ajeno y hay perros) --}}
                    @if(!$esMiPerfil && $compatibilidad !== null)
                    <div class="soft-card p-5">
                        <h3 class="h-display text-base text-ink-700/60 mb-3">Compatibilidad</h3>
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <div class="compat-bar h-3">
                                    <div class="compat-fill h-full" style="width: {{ $compatibilidad }}%"></div>
                                </div>
                            </div>
                            <span class="h-display text-3xl {{ $compatibilidad >= 80 ? 'text-sage-600' : 'text-brand-500' }}">
                                {{ $compatibilidad }}%
                            </span>
                        </div>
                        <p class="text-xs text-ink-700/45 mt-2">
                            @if($compatibilidad >= 85) 🌟 Pareja ideal — ¡seguro que se llevarían genial!
                            @elseif($compatibilidad >= 70) 👍 Buena compatibilidad
                            @elseif($compatibilidad >= 55) 🤔 Compatibilidad media — puede funcionar
                            @else ⚡ Personalidades muy distintas
                            @endif
                        </p>
                    </div>
                    @endif

                    {{-- Mi plan (solo en mi perfil) --}}
                    @if($esMiPerfil)
                    <div class="soft-card p-5">
                        <h3 class="h-display text-base text-ink-700/60 mb-3">Mi plan</h3>
                        <div class="flex items-center justify-between">
                            <span class="pill {{ $perfil->plan === 'premium' ? 'pill-pink' : 'pill-cream' }} capitalize text-sm">
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

                {{-- Columna derecha (2/3): perfil del perro --}}
                <div class="lg:col-span-2 space-y-5">

                    @if($perro)

                    {{-- Foto grande del perro --}}
                    <div class="soft-card overflow-hidden">
                        <div class="relative aspect-[16/8] bg-gradient-to-br from-brand-100 via-cream-200 to-sage-100">
                            @if($perro->foto_principal)
                            <img src="{{ $perro->foto_principal }}"
                                 class="absolute inset-0 w-full h-full object-cover"
                                 alt="{{ $perro->nombre }}"
                                 onerror="this.style.display='none'">
                            @else
                            <div class="absolute inset-0 flex items-center justify-center text-8xl">🐶</div>
                            @endif

                            {{-- Nombre del perro sobre la foto --}}
                            <div class="absolute bottom-0 inset-x-0 p-5 bg-gradient-to-t from-ink-900/70 to-transparent">
                                <h2 class="h-display text-3xl text-white">{{ $perro->nombre }}</h2>
                                <p class="text-white/75 text-sm">{{ $perro->raza }} · {{ $perro->edad_texto }}
                                    @if($perro->sexo) · {{ $perro->sexo === 'macho' ? '♂ Macho' : '♀ Hembra' }}@endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Descripción --}}
                    @if($perro->descripcion)
                    <div class="soft-card p-5">
                        <h3 class="h-display text-base text-ink-700/60 mb-2">Sobre {{ $perro->nombre }}</h3>
                        <p class="text-[14px] text-ink-700/80 leading-relaxed">{{ $perro->descripcion }}</p>
                    </div>
                    @endif

                    {{-- Datos + personalidad en grid --}}
                    <div class="grid sm:grid-cols-2 gap-5">

                        {{-- Datos físicos --}}
                        <div class="soft-card p-5">
                            <h3 class="h-display text-base text-ink-700/60 mb-4">Datos</h3>
                            <div class="space-y-2.5">
                                @if($perro->peso_kg)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-ink-700/60">Tamaño</span>
                                    <span class="pill pill-cream">{{ $perro->tamano }}</span>
                                </div>
                                @endif
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-ink-700/60">Energía</span>
                                    <span class="pill pill-pink">
                                        {{ ['','😴','🚶','🐕','⚡','🚀'][$perro->energia] }}
                                        {{ $perro->energia_texto }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-ink-700/60">Esterilizado/a</span>
                                    <span class="{{ $perro->esterilizado ? 'text-sage-600 font-semibold' : 'text-ink-700/40' }}">
                                        {{ $perro->esterilizado ? '✓ Sí' : 'No' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-ink-700/60">Vacunado/a</span>
                                    <span class="{{ $perro->vacunado ? 'text-sage-600 font-semibold' : 'text-ink-700/40' }}">
                                        {{ $perro->vacunado ? '✓ Sí' : 'No' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-ink-700/60">Con perros pequeños</span>
                                    <span class="{{ $perro->compatible_pequenos ? 'text-sage-600 font-semibold' : 'text-ink-700/40' }}">
                                        {{ $perro->compatible_pequenos ? '✓ Sí' : 'No' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-ink-700/60">Con perros grandes</span>
                                    <span class="{{ $perro->compatible_grandes ? 'text-sage-600 font-semibold' : 'text-ink-700/40' }}">
                                        {{ $perro->compatible_grandes ? '✓ Sí' : 'No' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Carácter --}}
                        <div class="soft-card p-5">
                            <h3 class="h-display text-base text-ink-700/60 mb-4">Personalidad</h3>
                            @if($perro->caracter && count($perro->caracter))
                            <div class="flex flex-wrap gap-2">
                                @foreach($perro->caracter as $rasgo)
                                <span class="pill pill-sage capitalize text-sm">{{ $rasgo }}</span>
                                @endforeach
                            </div>
                            @else
                            <p class="text-sm text-ink-700/40 italic">Sin rasgos definidos aún</p>
                            @endif

                            {{-- Barra visual de energía --}}
                            <div class="mt-5 pt-4 border-t border-cream-200">
                                <p class="text-xs text-ink-700/50 mb-2 font-medium">Nivel de energía</p>
                                <div class="flex gap-1.5">
                                    @for($i = 1; $i <= 5; $i++)
                                    <div class="flex-1 h-2.5 rounded-full {{ $i <= $perro->energia ? 'bg-brand-400' : 'bg-cream-300' }}"></div>
                                    @endfor
                                </div>
                                <p class="text-xs text-ink-700/45 mt-1.5 text-right">{{ $perro->energia_texto }}</p>
                            </div>
                        </div>

                    </div>

                    {{-- Galería adicional si hay fotos --}}
                    @if(count($galeria) > 0)
                    <div class="soft-card p-5">
                        <h3 class="h-display text-base text-ink-700/60 mb-4">Más fotos</h3>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach($galeria as $foto)
                            <div class="aspect-square rounded-2xl overflow-hidden bg-cream-200">
                                <img src="{{ $foto }}" class="w-full h-full object-cover" alt="">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- CTA si es mi propio perfil --}}
                    @if($esMiPerfil)
                    <div class="soft-card-flat p-5 flex items-center justify-between flex-wrap gap-3">
                        <div>
                            <p class="font-semibold text-ink-800 text-sm">¿Quieres mejorar el perfil de {{ $perro->nombre }}?</p>
                            <p class="text-xs text-ink-700/50 mt-0.5">Añade más fotos, actualiza su descripción o ajusta su carácter</p>
                        </div>
                        <a href="{{ route('perfil') }}?paso=2" class="btn-soft text-sm">Editar perro →</a>
                    </div>
                    @endif

                    @else

                    {{-- Sin perro registrado --}}
                    <div class="soft-card p-10 text-center">
                        <div class="text-6xl mb-4 animate-float-slow inline-block">🐶</div>
                        <h3 class="h-display text-2xl text-ink-800">
                            @if($esMiPerfil) Aún no has registrado a tu perro
                            @else Este usuario no tiene perro registrado aún
                            @endif
                        </h3>
                        <p class="text-sm text-ink-700/55 mt-2 mb-6">
                            @if($esMiPerfil) El perfil de tu perro es lo primero que ven los demás usuarios
                            @else Puede que lo añada pronto
                            @endif
                        </p>
                        @if($esMiPerfil)
                        <a href="{{ route('perfil') }}" class="btn-primary">Añadir mi perro</a>
                        @endif
                    </div>

                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
