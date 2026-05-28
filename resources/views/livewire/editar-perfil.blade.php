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

        <div class="w-full max-w-3xl mx-auto">

            {{-- Header --}}
            <div class="mb-8 animate-fade-up">
                <p class="pill pill-sage mb-3">🐾 Tu rincón</p>
                <h1 class="h-display text-4xl sm:text-5xl leading-none">Mi perfil</h1>
                <p class="text-ink-700/60 mt-2 text-[15px]">Cuida tu información y la de tu compañero.</p>
            </div>

            {{-- ══════════════════════════════════════════
                 STEPPER — indicador de pasos
            ══════════════════════════════════════════ --}}
            <div class="flex items-center gap-0 mb-10 animate-fade-up" style="animation-delay:50ms">
                @php
                    $pasos = [
                        1 => ['icon' => '👤', 'label' => 'Tu perfil'],
                        2 => ['icon' => '🐾', 'label' => 'Tu perro'],
                        3 => ['icon' => '✓',  'label' => '¡Listo!'],
                    ];
                @endphp

                @foreach($pasos as $n => $info)
                <button wire:click="irPaso({{ $n }})"
                        class="flex flex-col items-center gap-1.5 group transition-all relative"
                        @if($n > $paso) disabled @endif>

                    <div class="w-11 h-11 rounded-full flex items-center justify-center text-lg font-bold transition-all
                        {{ $paso === $n
                            ? 'bg-brand-500 text-white shadow-lift scale-110'
                            : ($paso > $n
                                ? 'bg-sage-500 text-white'
                                : 'bg-cream-200 text-ink-700/40') }}">
                        {{ $info['icon'] }}
                    </div>

                    <span class="text-[12px] font-semibold
                        {{ $paso === $n ? 'text-brand-600' : ($paso > $n ? 'text-sage-600' : 'text-ink-700/35') }}">
                        {{ $info['label'] }}
                    </span>
                </button>

                @if($n < count($pasos))
                <div class="flex-1 h-0.5 mx-3 mb-5 rounded-full transition-all
                     {{ $paso > $n ? 'bg-sage-400' : 'bg-cream-300' }}">
                </div>
                @endif
                @endforeach
            </div>

            {{-- ══════════════════════════════════════════
                 PASO 1 — Perfil del usuario
            ══════════════════════════════════════════ --}}
            @if($paso === 1)
            <div class="soft-card p-7 sm:p-9 animate-pop-in space-y-7">

                {{-- Zona foto de perfil --}}
                <div class="flex flex-col items-center gap-4">
                    <div class="relative group">
                        {{-- Preview / avatar actual --}}
                        <div class="w-28 h-28 rounded-full overflow-hidden ring-4 ring-brand-100 shadow-soft bg-gradient-to-br from-brand-200 to-sage-200 flex items-center justify-center text-5xl">
                            @if($fotoAvatar)
                                <img src="{{ $fotoAvatar->temporaryUrl() }}" class="w-full h-full object-cover" alt="Preview">
                            @elseif($avatarActual)
                                <img src="{{ $avatarActual }}" class="w-full h-full object-cover" alt="Avatar">
                            @else
                                <span class="text-4xl font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            @endif
                        </div>

                        {{-- Botón cambiar encima --}}
                        <label for="fotoAvatar"
                               class="absolute inset-0 rounded-full flex items-center justify-center
                                      bg-ink-900/0 hover:bg-ink-900/40 transition-all cursor-pointer group-hover:opacity-100">
                            <span class="text-white opacity-0 group-hover:opacity-100 transition-all text-xs font-bold text-center leading-tight px-1">
                                Cambiar<br>foto
                            </span>
                        </label>
                        <input id="fotoAvatar" type="file" wire:model="fotoAvatar" accept="image/*" class="hidden">
                    </div>

                    <div class="text-center">
                        <p class="text-sm font-semibold text-ink-700">
                            @if($fotoAvatar) ✅ Foto lista para guardar @else Tu foto de perfil @endif
                        </p>
                        <p class="text-xs text-ink-700/45 mt-0.5">JPG, PNG o WEBP · máx. 3 MB</p>
                    </div>

                    {{-- Zona drop alternativa si no hay foto --}}
                    @if(!$fotoAvatar && !$avatarActual)
                    <label for="fotoAvatar"
                           class="w-full border-2 border-dashed border-brand-200 rounded-2xl px-6 py-5
                                  flex flex-col items-center gap-2 cursor-pointer hover:border-brand-400
                                  hover:bg-brand-50/40 transition-all text-center">
                        <span class="text-3xl">📸</span>
                        <span class="text-sm font-semibold text-brand-500">Sube una foto tuya</span>
                        <span class="text-xs text-ink-700/45">Haz clic o arrastra aquí · Los demás dueños te verán mejor</span>
                    </label>
                    @endif

                    @error('fotoAvatar')
                    <p class="text-sm text-red-500 font-medium flex items-center gap-1.5">
                        <span>⚠️</span> {{ $message }}
                    </p>
                    @enderror

                    {{-- Loading foto --}}
                    <div wire:loading wire:target="fotoAvatar" class="text-xs text-ink-700/50 flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full border-2 border-brand-400 border-t-transparent animate-spin"></span>
                        Procesando imagen...
                    </div>
                </div>

                <div class="h-px bg-cream-200"></div>

                {{-- Campos --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="field-label">Nombre <span class="text-red-400">*</span></label>
                        <input type="text" wire:model.blur="name"
                               placeholder="Tu nombre completo"
                               class="field @error('name') !ring-red-300 @enderror">
                        @error('name')
                        <p class="text-sm text-red-500 font-medium mt-1.5 flex items-center gap-1">
                            <span>⚠️</span> {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <div>
                        <label class="field-label">Email <span class="text-red-400">*</span></label>
                        <input type="email" wire:model.blur="email"
                               placeholder="tu@email.com"
                               class="field @error('email') !ring-red-300 @enderror">
                        @error('email')
                        <p class="text-sm text-red-500 font-medium mt-1.5 flex items-center gap-1">
                            <span>⚠️</span> {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="field-label">Tu ciudad o barrio</label>
                        <input type="text" wire:model.blur="ciudad"
                               placeholder="Ej: Chamberí, Madrid"
                               class="field">
                    </div>

                    <div class="md:col-span-2">
                        <label class="field-label">
                            Cuéntanos algo sobre ti
                            <span class="text-ink-700/40 font-normal ml-1">— opcional</span>
                        </label>
                        <textarea wire:model.blur="bio" rows="3"
                                  placeholder="Soy dueño de... salimos por... buscamos..."
                                  class="field resize-none @error('bio') !ring-red-300 @enderror"></textarea>
                        @error('bio')
                        <p class="text-sm text-red-500 font-medium mt-1.5 flex items-center gap-1">
                            <span>⚠️</span> {{ $message }}
                        </p>
                        @enderror
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="flex items-center justify-between pt-2">
                    <span class="text-xs text-ink-700/40">Paso 1 de 2</span>
                    <button wire:click="irPaso(2)" class="btn-primary">
                        <span wire:loading.remove wire:target="irPaso(2)">
                            Siguiente: mi perro →
                        </span>
                        <span wire:loading wire:target="irPaso(2)" class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
                            Guardando...
                        </span>
                    </button>
                </div>
            </div>

            {{-- Enlace "solo guardar" --}}
            <div class="text-center mt-4">
                <button wire:click="guardarPerfil" class="text-sm text-ink-700/50 hover:text-brand-600 transition-colors font-medium">
                    Guardar sin avanzar
                </button>
            </div>


            {{-- ══════════════════════════════════════════
                 PASO 2 — Perfil del perro
            ══════════════════════════════════════════ --}}
            @elseif($paso === 2)
            <div class="soft-card p-7 sm:p-9 animate-pop-in space-y-7">

                {{-- Zona foto del perro --}}
                <div class="flex flex-col items-center gap-4">
                    <div class="relative group">
                        <div class="w-36 h-36 rounded-[1.5rem] overflow-hidden ring-4 ring-brand-100 shadow-soft bg-gradient-to-br from-brand-100 via-cream-200 to-sage-100 flex items-center justify-center">
                            @if($fotoPerro)
                                <img src="{{ $fotoPerro->temporaryUrl() }}" class="w-full h-full object-cover" alt="Preview">
                            @elseif($fotoPerroActual)
                                <img src="{{ $fotoPerroActual }}" class="w-full h-full object-cover" alt="Foto del perro">
                            @else
                                <span class="text-6xl">🐾</span>
                            @endif
                        </div>
                        <label for="fotoPerro"
                               class="absolute inset-0 rounded-[1.5rem] flex items-center justify-center
                                      bg-ink-900/0 hover:bg-ink-900/35 transition-all cursor-pointer">
                            <span class="text-white opacity-0 group-hover:opacity-100 transition-all text-xs font-bold text-center leading-tight px-2">
                                Cambiar<br>foto
                            </span>
                        </label>
                        <input id="fotoPerro" type="file" wire:model="fotoPerro" accept="image/*" class="hidden">
                    </div>

                    <div class="text-center">
                        <p class="text-sm font-semibold text-ink-700">
                            @if($fotoPerro) ✅ Foto lista para guardar @else Foto de {{ $perroNombre ?: 'tu perro' }} @endif
                        </p>
                        <p class="text-xs text-ink-700/45 mt-0.5">JPG, PNG o WEBP · máx. 3 MB</p>
                    </div>

                    @if(!$fotoPerro && !$fotoPerroActual)
                    <label for="fotoPerro"
                           class="w-full border-2 border-dashed border-brand-200 rounded-2xl px-6 py-5
                                  flex flex-col items-center gap-2 cursor-pointer hover:border-brand-400
                                  hover:bg-brand-50/40 transition-all text-center">
                        <span class="text-3xl">🐶</span>
                        <span class="text-sm font-semibold text-brand-500">Sube una foto de tu perro</span>
                        <span class="text-xs text-ink-700/45">Haz clic o arrastra aquí · Las fotos reales generan mucho más interés</span>
                    </label>
                    @endif

                    @error('fotoPerro')
                    <p class="text-sm text-red-500 font-medium flex items-center gap-1.5">
                        <span>⚠️</span> {{ $message }}
                    </p>
                    @enderror

                    <div wire:loading wire:target="fotoPerro" class="text-xs text-ink-700/50 flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full border-2 border-brand-400 border-t-transparent animate-spin"></span>
                        Procesando imagen...
                    </div>
                </div>

                <div class="h-px bg-cream-200"></div>

                {{-- Datos básicos --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="field-label">Nombre del perro <span class="text-red-400">*</span></label>
                        <input type="text" wire:model.blur="perroNombre"
                               placeholder="¿Cómo se llama?"
                               class="field @error('perroNombre') !ring-red-300 @enderror">
                        @error('perroNombre')
                        <p class="text-sm text-red-500 font-medium mt-1.5 flex items-center gap-1"><span>⚠️</span> {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="field-label">Raza</label>
                        <input type="text" wire:model.blur="perroRaza"
                               placeholder="Ej: Golden Retriever"
                               class="field">
                    </div>

                    <div>
                        <label class="field-label">Edad (años)</label>
                        <input type="number" wire:model.blur="perroEdadAnios" min="0" max="25"
                               placeholder="0"
                               class="field @error('perroEdadAnios') !ring-red-300 @enderror">
                        @error('perroEdadAnios')
                        <p class="text-sm text-red-500 font-medium mt-1.5 flex items-center gap-1"><span>⚠️</span> {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="field-label">Peso (kg)</label>
                        <input type="number" wire:model.blur="perroPesoKg" min="0" step="0.5"
                               placeholder="0"
                               class="field @error('perroPesoKg') !ring-red-300 @enderror">
                        @error('perroPesoKg')
                        <p class="text-sm text-red-500 font-medium mt-1.5 flex items-center gap-1"><span>⚠️</span> {{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Sexo como selector visual --}}
                <div>
                    <label class="field-label">Sexo</label>
                    <div class="flex gap-3">
                        @foreach(['hembra' => ['emoji' => '♀️', 'label' => 'Hembra'], 'macho' => ['emoji' => '♂️', 'label' => 'Macho']] as $val => $opt)
                        <button type="button" wire:click="$set('perroSexo', '{{ $val }}')"
                                class="flex-1 flex items-center justify-center gap-2 py-3 rounded-2xl text-sm font-semibold border-2 transition-all
                                       {{ $perroSexo === $val
                                           ? 'border-brand-400 bg-brand-50 text-brand-700'
                                           : 'border-cream-300 bg-cream-50 text-ink-700/60 hover:border-brand-200' }}">
                            <span class="text-xl">{{ $opt['emoji'] }}</span> {{ $opt['label'] }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Checkboxes --}}
                <div class="flex gap-5 flex-wrap">
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input type="checkbox" wire:model.live="perroEsterilizado"
                               class="w-5 h-5 rounded-md text-brand-500 focus:ring-brand-400 border-cream-300">
                        <span class="text-sm text-ink-700 font-medium group-hover:text-ink-900">Esterilizado/a</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input type="checkbox" wire:model.live="perroVacunado"
                               class="w-5 h-5 rounded-md text-brand-500 focus:ring-brand-400 border-cream-300">
                        <span class="text-sm text-ink-700 font-medium group-hover:text-ink-900">Vacunado/a</span>
                    </label>
                </div>

                {{-- Descripción --}}
                <div>
                    <label class="field-label">
                        Cuéntanos cómo es
                        <span class="text-ink-700/40 font-normal ml-1">— lo ven otros usuarios</span>
                    </label>
                    <textarea wire:model.blur="perroDescripcion" rows="3"
                              placeholder="Qué le gusta hacer, cómo se lleva con otros perros, horarios de paseo..."
                              class="field resize-none"></textarea>
                    <p class="text-[11px] text-ink-700/40 mt-1 ml-1">Máx. 600 caracteres</p>
                </div>

                {{-- Energía --}}
                <div>
                    <label class="field-label">Nivel de energía</label>
                    <div class="grid grid-cols-5 gap-2">
                        @php $energiaLabels = [1 => '😴', 2 => '🚶', 3 => '🐕', 4 => '⚡', 5 => '🚀']; @endphp
                        @foreach($energiaLabels as $val => $emoji)
                        <button type="button" wire:click="$set('perroEnergia', {{ $val }})"
                                class="flex flex-col items-center gap-1 py-2.5 rounded-2xl text-lg border-2 transition-all
                                       {{ $perroEnergia === $val
                                           ? 'border-brand-400 bg-brand-50 scale-105 shadow-soft'
                                           : 'border-cream-300 bg-cream-50 hover:border-brand-200' }}">
                            {{ $emoji }}
                            <span class="text-[10px] font-semibold text-ink-700/60">
                                {{ ['','Baja','Baja','Media','Alta','Muy alta'][$val] }}
                            </span>
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Carácter --}}
                <div>
                    <label class="field-label">Carácter <span class="text-ink-700/40 font-normal ml-1">— elige varios</span></label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach($opcionesCaracter as $rasgo)
                        <button type="button" wire:click="toggleCaracter('{{ $rasgo }}')"
                                class="px-4 py-1.5 rounded-full text-sm font-semibold transition-all capitalize
                                       {{ in_array($rasgo, $perroCaracter)
                                           ? 'bg-sage-500 text-white shadow-soft'
                                           : 'bg-cream-100 text-ink-700/70 ring-1 ring-cream-300 hover:ring-sage-300' }}">
                            {{ ucfirst($rasgo) }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="flex items-center justify-between pt-2">
                    <button wire:click="irPaso(1)" class="btn-soft text-sm">
                        ← Volver
                    </button>
                    <button wire:click="irPaso(3)" class="btn-primary">
                        <span wire:loading.remove wire:target="irPaso(3)">
                            Guardar y terminar ✓
                        </span>
                        <span wire:loading wire:target="irPaso(3)" class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
                            Guardando...
                        </span>
                    </button>
                </div>
            </div>

            <div class="text-center mt-4">
                <button wire:click="guardarPerro" class="text-sm text-ink-700/50 hover:text-brand-600 transition-colors font-medium">
                    Guardar sin avanzar
                </button>
            </div>


            {{-- ══════════════════════════════════════════
                 PASO 3 — ¡Listo!
            ══════════════════════════════════════════ --}}
            @elseif($paso === 3)
            <div class="soft-card p-10 animate-pop-in text-center space-y-5">
                <div class="text-7xl animate-float-slow inline-block">🎉</div>
                <div>
                    <h2 class="h-display text-3xl text-ink-900">¡Todo listo!</h2>
                    <p class="text-ink-700/60 mt-2 text-[15px] max-w-sm mx-auto leading-relaxed">
                        Tu perfil y el de tu perro están actualizados. Ya puedes explorar la manada.
                    </p>
                </div>

                {{-- Mini resumen --}}
                <div class="flex items-center justify-center gap-6 py-4">
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-16 h-16 rounded-full overflow-hidden ring-4 ring-brand-100 shadow-soft bg-gradient-to-br from-brand-200 to-sage-200 flex items-center justify-center">
                            @if($avatarActual)
                                <img src="{{ $avatarActual }}" class="w-full h-full object-cover" alt="">
                            @else
                                <span class="text-xl font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <p class="text-xs font-semibold text-ink-700/60">{{ auth()->user()->name }}</p>
                    </div>

                    <div class="text-2xl text-ink-700/30">+</div>

                    <div class="flex flex-col items-center gap-2">
                        <div class="w-16 h-16 rounded-[1rem] overflow-hidden ring-4 ring-sage-100 shadow-soft bg-gradient-to-br from-brand-100 to-sage-100 flex items-center justify-center">
                            @if($fotoPerroActual)
                                <img src="{{ $fotoPerroActual }}" class="w-full h-full object-cover" alt="">
                            @else
                                <span class="text-3xl">🐾</span>
                            @endif
                        </div>
                        <p class="text-xs font-semibold text-ink-700/60">{{ $perroNombre ?: 'Tu perro' }}</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                    <a href="{{ route('mi-perfil') }}" class="btn-primary">
                        Ver mi perfil →
                    </a>
                    <a href="{{ route('discover') }}" class="btn-soft">
                        Explorar perros
                    </a>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
