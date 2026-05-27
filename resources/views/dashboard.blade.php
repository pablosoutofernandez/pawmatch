<x-app-layout>
    <div class="w-full grid grid-cols-12 justify-start items-start gap-4">
        @include('partials.sidebar')

        <div class="w-full col-span-12 md:col-span-11 min-h-screen flex flex-col justify-start items-start pt-6 md:pt-10 px-4 sm:px-6 lg:px-8 bg-slate-50/50">

            {{-- Mensajes flash --}}
            @if(session('success'))
                <div class="w-full max-w-5xl mx-auto mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-600 px-6 py-4 rounded-2xl shadow-sm animate-fade-in-down">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <div class="w-full max-w-6xl mx-auto space-y-6">

                {{-- Saludo --}}
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">
                            ¡Hola, {{ explode(' ', $user->name)[0] }}! 👋
                        </h1>
                        <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold mt-1">
                            Bienvenido de vuelta a PawMatch
                        </p>
                    </div>
                </div>

                {{-- Stats grid --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @php
                    $statCards = [
                        ['emoji' => '🐶', 'label' => 'Perros registrados', 'value' => $stats['perros_total']],
                        ['emoji' => '🟢', 'label' => 'Paseando ahora',     'value' => $stats['activos_ahora']],
                        ['emoji' => '⭐', 'label' => 'Mis PawPoints',       'value' => $stats['mis_puntos']],
                        ['emoji' => '❤️', 'label' => 'Mis matches',         'value' => $stats['mis_matches']],
                    ];
                    @endphp

                    @foreach($statCards as $card)
                    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.04)] border border-gray-100 p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl">{{ $card['emoji'] }}</span>
                            <span class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Total</span>
                        </div>
                        <div class="text-3xl font-bold text-slate-800">{{ $card['value'] }}</div>
                        <p class="text-xs text-slate-500 mt-1">{{ $card['label'] }}</p>
                    </div>
                    @endforeach
                </div>

                {{-- Mi perro --}}
                <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="font-bold text-slate-800">Mi perro</h2>
                        <a href="{{ route('perfil') }}" class="text-xs text-brand-600 font-bold hover:underline">
                            Editar →
                        </a>
                    </div>

                    @if($miPerro)
                    <div class="p-6 flex items-center gap-5 flex-wrap">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-100 to-brand-200 flex items-center justify-center text-4xl flex-shrink-0">
                            🐾
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="text-xl font-bold text-slate-800">{{ $miPerro->nombre }}</h3>
                            <p class="text-sm text-slate-500">
                                {{ $miPerro->raza }} · {{ $miPerro->edad_texto }}
                            </p>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
                                    {{ $miPerro->tamano }}
                                </span>
                                <span class="text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-amber-100 text-amber-700">
                                    ⚡ {{ $miPerro->energia_texto }}
                                </span>
                                @if($miPerro->vacunado)
                                <span class="text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700">
                                    ✓ Vacunado
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($miPerro->caracter)
                    <div class="px-6 pb-5 flex flex-wrap gap-1.5">
                        @foreach($miPerro->caracter as $rasgo)
                        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full border border-brand-200 text-brand-700 bg-brand-50 capitalize">
                            {{ $rasgo }}
                        </span>
                        @endforeach
                    </div>
                    @endif

                    @else
                    <div class="p-8 text-center">
                        <div class="text-5xl mb-3">🐶</div>
                        <p class="font-bold text-slate-700">Aún no has registrado tu perro</p>
                        <p class="text-sm text-slate-500 mt-1 mb-4">Añade el perfil de tu perro para empezar a hacer matches</p>
                        <a href="{{ route('perfil') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white bg-brand-500 hover:bg-brand-600 transition-colors">
                            Añadir mi perro
                        </a>
                    </div>
                    @endif
                </div>

                {{-- Sugerencias --}}
                <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.04)] border border-gray-100">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="font-bold text-slate-800">Perros compatibles cerca</h2>
                        <a href="{{ route('discover') }}" class="text-xs text-brand-600 font-bold hover:underline">
                            Ver todos →
                        </a>
                    </div>

                    @if($sugerencias->isEmpty())
                    <div class="p-8 text-center text-slate-500">
                        <p>No hay perros registrados cerca aún.</p>
                    </div>
                    @else
                    <div class="divide-y divide-slate-100">
                        @foreach($sugerencias as $perro)
                        <div class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 transition-colors">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-2xl flex-shrink-0">
                                🐶
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-baseline gap-2">
                                    <span class="font-bold text-slate-800">{{ $perro->nombre }}</span>
                                    <span class="text-xs text-slate-400">{{ $perro->distancia }}</span>
                                </div>
                                <p class="text-xs text-slate-500 truncate">
                                    {{ $perro->raza }} · {{ $perro->edad_texto }} · con {{ $perro->dueno->name }}
                                </p>
                                <div class="mt-1.5 compat-bar w-32">
                                    <div class="compat-fill" style="width: {{ $perro->compatibilidad }}%"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-sm font-bold {{ $perro->compatibilidad >= 80 ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $perro->compatibilidad }}%
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
