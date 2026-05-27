<div class="w-full grid grid-cols-12 justify-start items-start gap-4">
    @include('partials.sidebar')

    <div class="w-full col-span-12 md:col-span-11 min-h-screen flex flex-col justify-start items-start pt-6 md:pt-10 px-4 sm:px-6 lg:px-8 bg-slate-50/50">

        {{-- Flash --}}
        @if(session('success'))
            <div class="w-full max-w-4xl mx-auto mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-600 px-6 py-4 rounded-2xl shadow-sm animate-fade-in-down">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        <div class="w-full max-w-4xl mx-auto">

            {{-- Header --}}
            <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Mi Perfil</h2>
                    <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold mt-1">
                        Gestiona tu información y la de tu perro
                    </p>
                </div>
                <span class="text-[11px] font-bold uppercase tracking-widest px-3 py-1 rounded-full bg-slate-100 text-slate-600">
                    ⭐ Plan {{ auth()->user()->plan }}
                </span>
            </div>

            {{-- Tabs --}}
            <div class="flex gap-1 mb-6 bg-slate-200/60 p-1 rounded-xl w-fit">
                @foreach(['usuario' => 'Mi perfil', 'perro' => 'Mi perro', 'preferencias' => 'Preferencias', 'seguridad' => 'Seguridad'] as $key => $label)
                <button wire:click="cambiarTab('{{ $key }}')"
                        class="px-5 py-2 rounded-lg text-xs font-bold uppercase tracking-widest transition-all
                               {{ $tabActiva === $key
                                    ? 'bg-white text-slate-800 shadow-sm'
                                    : 'text-slate-500 hover:text-slate-800' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            {{-- ═══ TAB: USUARIO ═══ --}}
            @if($tabActiva === 'usuario')
            <form wire:submit.prevent="guardarPerfil"
                  class="bg-white rounded-2xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">

                <h3 class="font-bold text-slate-800 mb-5">Información personal</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 ml-1">Nombre</label>
                        <input type="text" wire:model.live.blur="name"
                               class="w-full px-4 py-2.5 bg-slate-50 border {{ $errors->has('name') ? 'border-red-500' : 'border-gray-200' }} rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        @error('name')<p class="text-[11px] text-red-500 font-semibold mt-1 ml-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 ml-1">Email</label>
                        <input type="email" wire:model.live.blur="email"
                               class="w-full px-4 py-2.5 bg-slate-50 border {{ $errors->has('email') ? 'border-red-500' : 'border-gray-200' }} rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        @error('email')<p class="text-[11px] text-red-500 font-semibold mt-1 ml-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 ml-1">Ciudad / Barrio</label>
                        <input type="text" wire:model.live.blur="ciudad" placeholder="Ej: Salamanca, Madrid"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 ml-1">Biografía</label>
                        <textarea wire:model.live.blur="bio" rows="3"
                                  placeholder="Cuéntanos algo sobre ti y tu perro..."
                                  class="w-full px-4 py-2.5 bg-slate-50 border {{ $errors->has('bio') ? 'border-red-500' : 'border-gray-200' }} rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all resize-none"></textarea>
                        @error('bio')<p class="text-[11px] text-red-500 font-semibold mt-1 ml-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="bg-brand-500 hover:bg-brand-600 text-white font-bold py-2.5 px-6 rounded-xl text-sm shadow-lg shadow-brand-200 transition-all">
                        <span wire:loading.remove wire:target="guardarPerfil">Guardar cambios</span>
                        <span wire:loading wire:target="guardarPerfil">Guardando...</span>
                    </button>
                </div>
            </form>

            {{-- ═══ TAB: PERRO ═══ --}}
            @elseif($tabActiva === 'perro')
            <form wire:submit.prevent="guardarPerro"
                  class="bg-white rounded-2xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">

                <h3 class="font-bold text-slate-800 mb-5">Perfil de mi perro</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 ml-1">Nombre del perro</label>
                        <input type="text" wire:model.live.blur="perroNombre"
                               class="w-full px-4 py-2.5 bg-slate-50 border {{ $errors->has('perroNombre') ? 'border-red-500' : 'border-gray-200' }} rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        @error('perroNombre')<p class="text-[11px] text-red-500 font-semibold mt-1 ml-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 ml-1">Raza</label>
                        <input type="text" wire:model.live.blur="perroRaza" placeholder="Ej: Golden Retriever"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 ml-1">Edad (años)</label>
                        <input type="number" wire:model.live.blur="perroEdadAnios" min="0" max="25"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 ml-1">Peso (kg)</label>
                        <input type="number" wire:model.live.blur="perroPesoKg" min="0" step="0.5"
                               class="w-full px-4 py-2.5 bg-slate-50 border {{ $errors->has('perroPesoKg') ? 'border-red-500' : 'border-gray-200' }} rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        @error('perroPesoKg')<p class="text-[11px] text-red-500 font-semibold mt-1 ml-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 ml-1">Sexo</label>
                        <select wire:model.live="perroSexo"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            <option value="hembra">Hembra</option>
                            <option value="macho">Macho</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-3 pt-7">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="perroEsterilizado" class="rounded text-brand-500 focus:ring-brand-500">
                            <span class="text-sm text-slate-700 font-semibold">Esterilizado/a</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="perroVacunado" class="rounded text-brand-500 focus:ring-brand-500">
                            <span class="text-sm text-slate-700 font-semibold">Vacunado/a</span>
                        </label>
                    </div>
                </div>

                {{-- Energía --}}
                <div class="mt-5">
                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 ml-1 block mb-2">Nivel de energía</label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach([1 => 'Muy baja', 2 => 'Baja', 3 => 'Media', 4 => 'Alta', 5 => 'Muy alta'] as $valor => $label)
                        <button type="button" wire:click="$set('perroEnergia', {{ $valor }})"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider border transition-all
                                       {{ $perroEnergia === $valor
                                            ? 'bg-brand-500 text-white border-brand-500'
                                            : 'bg-white text-slate-600 border-slate-200 hover:border-brand-300' }}">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Carácter --}}
                <div class="mt-5">
                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 ml-1 block mb-2">Carácter (selecciona varios)</label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach($opcionesCaracter as $rasgo)
                        <button type="button" wire:click="toggleCaracter('{{ $rasgo }}')"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider border transition-all
                                       {{ in_array($rasgo, $perroCaracter)
                                            ? 'bg-brand-100 text-brand-700 border-brand-300'
                                            : 'bg-white text-slate-600 border-slate-200 hover:border-brand-300' }}">
                            {{ ucfirst($rasgo) }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="bg-brand-500 hover:bg-brand-600 text-white font-bold py-2.5 px-6 rounded-xl text-sm shadow-lg shadow-brand-200 transition-all">
                        <span wire:loading.remove wire:target="guardarPerro">
                            {{ $perroId ? 'Actualizar perro' : 'Crear perro' }}
                        </span>
                        <span wire:loading wire:target="guardarPerro">Guardando...</span>
                    </button>
                </div>
            </form>

            {{-- ═══ TAB: PREFERENCIAS ═══ --}}
            @elseif($tabActiva === 'preferencias')
            <div class="bg-white rounded-2xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
                <h3 class="font-bold text-slate-800 mb-5">Preferencias de matching</h3>
                <p class="text-sm text-slate-500">
                    Aquí podrás configurar tu radio de búsqueda, tamaño preferido y notificaciones.
                </p>
                <p class="text-xs text-slate-400 mt-3 italic">Sección en desarrollo</p>
            </div>

            {{-- ═══ TAB: SEGURIDAD ═══ --}}
            @elseif($tabActiva === 'seguridad')
            <div class="bg-white rounded-2xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
                <h3 class="font-bold text-slate-800 mb-5">Seguridad y privacidad</h3>
                <p class="text-sm text-slate-500 mb-4">
                    Cambiar contraseña, eliminar cuenta, exportar datos (RGPD).
                </p>
                <a href="{{ route('profile.edit') }}"
                   class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-4 rounded-xl text-sm transition-all">
                    Ir a configuración avanzada →
                </a>
            </div>
            @endif

        </div>
    </div>
</div>
