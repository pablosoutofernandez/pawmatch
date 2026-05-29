<div class="w-full flex items-start paw-canvas min-h-screen">
    @include('partials.sidebar')

    <div class="flex-1 min-w-0 min-h-screen pt-6 md:pt-10 px-4 sm:px-6 lg:px-10 pb-20">

        {{-- Flash --}}
        @if(session('success'))
            <div class="w-full max-w-3xl mx-auto mb-6 flex items-center gap-3 bg-sage-50 ring-1 ring-sage-200 text-sage-700 px-5 py-3.5 rounded-2xl shadow-soft animate-fade-in-down">
                <span class="text-lg">🎉</span>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        <div class="w-full max-w-3xl mx-auto">

            {{-- Header --}}
            <div class="mb-7 animate-fade-up">
                <p class="pill pill-sage mb-3">🐾 Tu rincón</p>
                <h1 class="h-display text-4xl sm:text-5xl leading-none">Editar perfil</h1>
                <p class="text-ink-700/60 mt-2 text-[15px]">Cuida tu información y la de tu compañero.</p>
            </div>

            {{-- ══════════════════════════════════════════
                 STEPPER — 2 pasos reales con barra de progreso
            ══════════════════════════════════════════ --}}
            @if($paso !== 3)
                @php
                    $pasos = [
                        1 => ['icon' => '👤', 'label' => 'Tu perfil', 'sub' => 'Quién eres'],
                        2 => ['icon' => '🐾', 'label' => 'Tu perro',  'sub' => 'Tu compañero'],
                    ];
                @endphp
                <div class="flex items-stretch gap-3 mb-8 animate-fade-up" style="animation-delay:50ms">
                    @foreach($pasos as $n => $info)
                        <button wire:click="irPaso({{ $n }})"
                                class="flex-1 flex items-center gap-3 px-4 py-3 rounded-2xl border-2 text-left transition-all cursor-pointer
                               {{ $paso === $n
                                    ? 'border-brand-400 bg-white shadow-soft'
                                    : 'border-sage-300 bg-sage-50/50 hover:border-sage-400' }}">
                    <span class="w-10 h-10 rounded-full flex items-center justify-center text-lg flex-shrink-0 transition-all
                        {{ $paso === $n ? 'bg-brand-500 text-white scale-105'
                            : ($paso > $n ? 'bg-sage-500 text-white' : 'bg-brand-200 text-brand-700') }}">
                        {{ $paso > $n ? '✓' : $info['icon'] }}
                    </span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold {{ $paso === $n ? 'text-brand-600' : 'text-sage-700' }}">
                                    {{ $info['label'] }}
                                </p>
                                <p class="text-[11px] text-ink-700/45 truncate">{{ $info['sub'] }}</p>
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- ══════════════════════════════════════════
                 PASO 1 — Perfil del usuario
            ══════════════════════════════════════════ --}}
            @if($paso === 1)
                <div class="soft-card p-7 sm:p-9 animate-pop-in space-y-7">

                    {{-- Foto de perfil --}}
                    <div class="flex items-center gap-5">
                        <div class="relative group flex-shrink-0">
                            <div class="w-24 h-24 rounded-full overflow-hidden ring-4 ring-brand-100 shadow-soft bg-gradient-to-br from-brand-200 to-sage-200 flex items-center justify-center">
                                @if($fotoAvatar)
                                    <img src="{{ $fotoAvatar->temporaryUrl() }}" class="w-full h-full object-cover" alt="Preview">
                                @elseif($avatarActual)
                                    <img src="{{ $avatarActual }}" class="w-full h-full object-cover" alt="Avatar">
                                @else
                                    <span class="text-3xl font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <label for="fotoAvatar"
                                   class="absolute inset-0 rounded-full flex items-center justify-center bg-ink-900/0 hover:bg-ink-900/40 transition-all cursor-pointer">
                                <span class="text-white opacity-0 group-hover:opacity-100 transition-all text-xs font-bold text-center leading-tight">Cambiar</span>
                            </label>
                            <input id="fotoAvatar" type="file" wire:model="fotoAvatar" accept="image/*" class="hidden">
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-ink-800">
                                @if($fotoAvatar) ✅ Foto lista para guardar @else Tu foto de perfil @endif
                            </p>
                            <p class="text-xs text-ink-700/50 mt-0.5">JPG, PNG o WEBP · máx. 3 MB</p>
                            <label for="fotoAvatar" class="inline-block mt-2 text-xs font-bold text-brand-500 hover:text-brand-600 cursor-pointer">
                                {{ ($avatarActual || $fotoAvatar) ? 'Cambiar foto' : '📸 Subir una foto' }}
                            </label>
                            <div wire:loading wire:target="fotoAvatar" class="text-xs text-ink-700/50 flex items-center gap-1.5 mt-1">
                                <span class="w-3 h-3 rounded-full border-2 border-brand-400 border-t-transparent animate-spin"></span>
                                Procesando…
                            </div>
                        </div>
                    </div>
                    @error('fotoAvatar')
                    <p class="text-sm text-red-500 font-medium flex items-center gap-1.5 -mt-3"><span>⚠️</span> {{ $message }}</p>
                    @enderror

                    <div class="h-px bg-cream-200"></div>

                    {{-- Campos --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="field-label">Nombre <span class="text-red-400">*</span></label>
                            <input type="text" wire:model.blur="name" placeholder="Tu nombre completo"
                                   class="field @error('name') !ring-red-300 @enderror">
                            @error('name')<p class="text-sm text-red-500 font-medium mt-1.5 flex items-center gap-1"><span>⚠️</span> {{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label">Email <span class="text-red-400">*</span></label>
                            <input type="email" wire:model.blur="email" placeholder="tu@email.com"
                                   class="field @error('email') !ring-red-300 @enderror">
                            @error('email')<p class="text-sm text-red-500 font-medium mt-1.5 flex items-center gap-1"><span>⚠️</span> {{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Tu ciudad o barrio</label>
                            <input type="text" wire:model.blur="ciudad" placeholder="Ej: Chamberí, Madrid" class="field">
                        </div>
                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between">
                                <label class="field-label">Cuéntanos algo sobre ti <span class="text-ink-700/40 font-normal">— opcional</span></label>
                                <span class="text-[11px] font-semibold {{ strlen($bio) > 500 ? 'text-red-500' : 'text-ink-700/40' }}">{{ strlen($bio) }}/500</span>
                            </div>
                            <textarea wire:model.live.debounce.300ms="bio" rows="3" maxlength="520"
                                      placeholder="Soy dueño de… salimos por… buscamos…"
                                      class="field resize-none @error('bio') !ring-red-300 @enderror"></textarea>
                            @error('bio')<p class="text-sm text-red-500 font-medium mt-1.5 flex items-center gap-1"><span>⚠️</span> {{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="flex items-center justify-between gap-3 pt-2">
                        <button wire:click="guardarPerfilySalir" class="text-sm text-ink-700/50 hover:text-brand-600 transition-colors font-medium">
                            Guardar y salir
                        </button>
                        <button wire:click="irPaso(2)" class="btn-primary">
                            <span wire:loading.remove wire:target="irPaso(2)">Siguiente: mi perro →</span>
                            <span wire:loading wire:target="irPaso(2)" class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin"></span>Guardando…
                        </span>
                        </button>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════
                     PASO 2 — Perfil del perro
                ══════════════════════════════════════════ --}}
            @elseif($paso === 2)
                <div class="soft-card p-7 sm:p-9 animate-pop-in space-y-7">

                    {{-- Foto del perro --}}
                    <div class="flex items-center gap-5">
                        <div class="relative group flex-shrink-0">
                            <div class="w-28 h-28 rounded-[1.5rem] overflow-hidden ring-4 ring-brand-100 shadow-soft bg-gradient-to-br from-brand-100 via-cream-200 to-sage-100 flex items-center justify-center">
                                @if($fotoPerro)
                                    <img src="{{ $fotoPerro->temporaryUrl() }}" class="w-full h-full object-cover" alt="Preview">
                                @elseif($fotoPerroActual)
                                    <img src="{{ $fotoPerroActual }}" class="w-full h-full object-cover" alt="Foto del perro">
                                @else
                                    <span class="text-5xl">🐾</span>
                                @endif
                            </div>
                            <label for="fotoPerro"
                                   class="absolute inset-0 rounded-[1.5rem] flex items-center justify-center bg-ink-900/0 hover:bg-ink-900/35 transition-all cursor-pointer">
                                <span class="text-white opacity-0 group-hover:opacity-100 transition-all text-xs font-bold text-center leading-tight">Cambiar</span>
                            </label>
                            <input id="fotoPerro" type="file" wire:model="fotoPerro" accept="image/*" class="hidden">
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-ink-800">
                                @if($fotoPerro) ✅ Foto lista para guardar @else Foto de {{ $perroNombre ?: 'tu perro' }} @endif
                            </p>
                            <p class="text-xs text-ink-700/50 mt-0.5">Las fotos reales generan mucho más interés</p>
                            <label for="fotoPerro" class="inline-block mt-2 text-xs font-bold text-brand-500 hover:text-brand-600 cursor-pointer">
                                {{ ($fotoPerroActual || $fotoPerro) ? 'Cambiar foto' : '🐶 Subir una foto' }}
                            </label>
                            <div wire:loading wire:target="fotoPerro" class="text-xs text-ink-700/50 flex items-center gap-1.5 mt-1">
                                <span class="w-3 h-3 rounded-full border-2 border-brand-400 border-t-transparent animate-spin"></span>
                                Procesando…
                            </div>
                        </div>
                    </div>
                    @error('fotoPerro')
                    <p class="text-sm text-red-500 font-medium flex items-center gap-1.5 -mt-3"><span>⚠️</span> {{ $message }}</p>
                    @enderror

                    <div class="h-px bg-cream-200"></div>

                    {{-- Datos básicos --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="field-label">Nombre del perro <span class="text-red-400">*</span></label>
                            <input type="text" wire:model.blur="perroNombre" placeholder="¿Cómo se llama?"
                                   class="field @error('perroNombre') !ring-red-300 @enderror">
                            @error('perroNombre')<p class="text-sm text-red-500 font-medium mt-1.5 flex items-center gap-1"><span>⚠️</span> {{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label">Raza</label>
                            <input type="text" wire:model.blur="perroRaza" placeholder="Ej: Golden Retriever (o mestizo)" class="field">
                        </div>
                        <div>
                            <label class="field-label">Edad</label>
                            <div class="flex gap-3">
                                <div class="flex-1">
                                    <div class="relative">
                                        <input type="number" wire:model.blur="perroEdadAnios" min="0" max="25" placeholder="0"
                                               class="field pr-14 @error('perroEdadAnios') !ring-red-300 @enderror">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-ink-700/50 pointer-events-none">años</span>
                                    </div>
                                    @error('perroEdadAnios')<p class="text-sm text-red-500 font-medium mt-1.5 flex items-center gap-1"><span>⚠️</span> {{ $message }}</p>@enderror
                                </div>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input type="number" wire:model.blur="perroEdadMeses" min="0" max="11" placeholder="0"
                                               class="field pr-16 @error('perroEdadMeses') !ring-red-300 @enderror">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-ink-700/50 pointer-events-none">meses</span>
                                    </div>
                                    @error('perroEdadMeses')<p class="text-sm text-red-500 font-medium mt-1.5 flex items-center gap-1"><span>⚠️</span> {{ $message }}</p>@enderror
                                </div>
                            </div>
                            @if($perroEdadAnios > 0 || $perroEdadMeses > 0)
                                <p class="text-xs text-ink-700/45 mt-1.5 font-medium">
                                    ≈ {{ $perroEdadAnios * 12 + $perroEdadMeses }} meses en total
                                    @if($perroEdadAnios > 0) · {{ $perroEdadAnios }} {{ $perroEdadAnios === 1 ? 'año' : 'años' }}@if($perroEdadMeses > 0) y {{ $perroEdadMeses }} {{ $perroEdadMeses === 1 ? 'mes' : 'meses' }}@endif@endif
                                </p>
                            @endif
                        </div>
                        <div>
                            <label class="field-label">Peso (kg)</label>
                            <input type="number" wire:model.blur="perroPesoKg" min="0" step="0.5" placeholder="Ej: 12.5"
                                   class="field @error('perroPesoKg') !ring-red-300 @enderror">
                            @error('perroPesoKg')<p class="text-sm text-red-500 font-medium mt-1.5 flex items-center gap-1"><span>⚠️</span> {{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Sexo --}}
                    <div>
                        <label class="field-label">Sexo</label>
                        <div class="flex gap-3">
                            @foreach(['hembra' => ['emoji' => '♀️', 'label' => 'Hembra'], 'macho' => ['emoji' => '♂️', 'label' => 'Macho']] as $val => $opt)
                                <button type="button" wire:click="$set('perroSexo', '{{ $val }}')"
                                        class="flex-1 flex items-center justify-center gap-2 py-3 rounded-2xl text-sm font-semibold border-2 transition-all
                                       {{ $perroSexo === $val ? 'border-brand-400 bg-brand-50 text-brand-700' : 'border-cream-300 bg-cream-50 text-ink-700/60 hover:border-brand-200' }}">
                                    <span class="text-xl">{{ $opt['emoji'] }}</span> {{ $opt['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Salud --}}
                    <div>
                        <label class="field-label">Salud</label>
                        <div class="grid grid-cols-2 gap-3">
                            @php $salud = ['perroEsterilizado' => 'Esterilizado/a', 'perroVacunado' => 'Vacunado/a']; @endphp
                            @foreach($salud as $model => $label)
                                <button type="button" wire:click="$toggle('{{ $model }}')"
                                        class="flex items-center gap-3 py-3 px-4 rounded-2xl border-2 text-sm font-semibold transition-all
                                       {{ $$model ? 'border-sage-400 bg-sage-50 text-sage-700' : 'border-cream-300 bg-cream-50 text-ink-700/55 hover:border-sage-200' }}">
                            <span class="w-5 h-5 rounded-md flex items-center justify-center flex-shrink-0 transition-all
                                {{ $$model ? 'bg-sage-500 text-white' : 'bg-cream-200' }}">
                                @if($$model)<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                            </span>
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="field-label">Cuéntanos cómo es <span class="text-ink-700/40 font-normal">— lo ven otros</span></label>
                            <span class="text-[11px] font-semibold {{ strlen($perroDescripcion) > 600 ? 'text-red-500' : 'text-ink-700/40' }}">{{ strlen($perroDescripcion) }}/600</span>
                        </div>
                        <textarea wire:model.live.debounce.300ms="perroDescripcion" rows="3" maxlength="620"
                                  placeholder="Qué le gusta, cómo se lleva con otros perros, horarios de paseo…"
                                  class="field resize-none @error('perroDescripcion') !ring-red-300 @enderror"></textarea>
                        @error('perroDescripcion')<p class="text-sm text-red-500 font-medium mt-1.5 flex items-center gap-1"><span>⚠️</span> {{ $message }}</p>@enderror
                    </div>

                    {{-- Energía --}}
                    <div>
                        <label class="field-label">Nivel de energía</label>
                        <div class="grid grid-cols-5 gap-2">
                            @php $energiaLabels = [1 => '😴', 2 => '🚶', 3 => '🐕', 4 => '⚡', 5 => '🚀']; @endphp
                            @foreach($energiaLabels as $val => $emoji)
                                <button type="button" wire:click="$set('perroEnergia', {{ $val }})"
                                        class="flex flex-col items-center gap-1 py-2.5 rounded-2xl text-lg border-2 transition-all
                                       {{ $perroEnergia === $val ? 'border-brand-400 bg-brand-50 scale-105 shadow-soft' : 'border-cream-300 bg-cream-50 hover:border-brand-200' }}">
                                    {{ $emoji }}
                                    <span class="text-[10px] font-semibold text-ink-700/60">{{ ['','Muy baja','Baja','Media','Alta','Muy alta'][$val] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Carácter con límite visible --}}
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="field-label">Carácter <span class="text-ink-700/40 font-normal">— máx. {{ $maxCaracter }}</span></label>
                            <span class="text-[11px] font-semibold {{ count($perroCaracter) >= $maxCaracter ? 'text-brand-500' : 'text-ink-700/40' }}">
                            {{ count($perroCaracter) }}/{{ $maxCaracter }}
                        </span>
                        </div>
                        <div class="flex gap-2 flex-wrap">
                            @foreach($opcionesCaracter as $rasgo)
                                @php $sel = in_array($rasgo, $perroCaracter); $lleno = !$sel && count($perroCaracter) >= $maxCaracter; @endphp
                                <button type="button" wire:click="toggleCaracter('{{ $rasgo }}')"
                                        @if($lleno) disabled @endif
                                        class="px-4 py-1.5 rounded-full text-sm font-semibold transition-all capitalize
                                       {{ $sel ? 'bg-sage-500 text-white shadow-soft'
                                            : ($lleno ? 'bg-cream-100 text-ink-700/30 ring-1 ring-cream-200 cursor-not-allowed'
                                                : 'bg-cream-100 text-ink-700/70 ring-1 ring-cream-300 hover:ring-sage-300') }}">
                                    {{ $sel ? '✓ ' : '' }}{{ ucfirst($rasgo) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="flex items-center justify-between gap-3 pt-2">
                        <button wire:click="irPaso(1)" class="btn-soft text-sm py-2">← Volver</button>
                        <button wire:click="irPaso(3)" class="btn-primary">
                            <span wire:loading.remove wire:target="irPaso(3)">Guardar y terminar ✓</span>
                            <span wire:loading wire:target="irPaso(3)" class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin"></span>Guardando…
                        </span>
                        </button>
                    </div>
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

                    <div class="flex items-center justify-center gap-6 py-4">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-16 h-16 rounded-full overflow-hidden ring-4 ring-brand-100 shadow-soft bg-gradient-to-br from-brand-200 to-sage-200 flex items-center justify-center">
                                @if($avatarActual)<img src="{{ $avatarActual }}" class="w-full h-full object-cover" alt="">
                                @else<span class="text-xl font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>@endif
                            </div>
                            <p class="text-xs font-semibold text-ink-700/60">{{ auth()->user()->name }}</p>
                        </div>
                        <div class="text-2xl text-brand-300">💞</div>
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-16 h-16 rounded-[1rem] overflow-hidden ring-4 ring-sage-100 shadow-soft bg-gradient-to-br from-brand-100 to-sage-100 flex items-center justify-center">
                                @if($fotoPerroActual)<img src="{{ $fotoPerroActual }}" class="w-full h-full object-cover" alt="">
                                @else<span class="text-3xl">🐾</span>@endif
                            </div>
                            <p class="text-xs font-semibold text-ink-700/60">{{ $perroNombre ?: 'Tu perro' }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                        <a href="{{ route('mi-perfil') }}" class="btn-primary">Ver mi perfil →</a>
                        <a href="{{ route('discover') }}" class="btn-soft">Explorar perros</a>
                    </div>
                    <button wire:click="$set('paso', 1)" class="text-sm text-ink-700/45 hover:text-brand-600 transition-colors font-medium">
                        ← Seguir editando
                    </button>
                </div>
            @endif

        </div>
    </div>
</div>