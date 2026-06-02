<div>
@if($abierto && $otro)
    {{-- Overlay full screen --}}
    <div class="fixed inset-0 z-[2000] flex items-center justify-center p-4 animate-fade-in-down"
         style="background: radial-gradient(circle at top, rgba(207,95,128,.55), rgba(0,0,0,.78));"
         wire:key="match-overlay-{{ $otro->id }}"
         x-data
         x-on:keydown.escape.window="$wire.cerrar()">

        {{-- Confeti CSS (decorativo) --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
            @for($i = 0; $i < 14; $i++)
                @php($cls = ['bg-brand-400','bg-sage-400','bg-cream-300','bg-brand-300','bg-white'][($i*3) % 5])
                @php($x = ($i * 7) % 100)
                @php($d = 0.6 + (($i * 17) % 100) / 100)
                @php($r = ($i * 47) % 360)
                <span class="absolute w-2 h-3 {{ $cls }} rounded-sm opacity-90"
                      style="left: {{ $x }}%; top: -10%;
                             animation: pawconfetti 2.4s ease-in {{ $d }}s forwards;
                             transform: rotate({{ $r }}deg);"></span>
            @endfor
        </div>

        <style>
            @keyframes pawconfetti {
                0%   { transform: translateY(0) rotate(0deg);   opacity: 0.9; }
                100% { transform: translateY(110vh) rotate(720deg); opacity: 0.2; }
            }
            @keyframes pop-heart {
                0%   { transform: scale(.6); opacity: 0; }
                60%  { transform: scale(1.15); opacity: 1; }
                100% { transform: scale(1); opacity: 1; }
            }
            .match-card { animation: pop-heart .55s cubic-bezier(.34,1.56,.64,1) both; }
        </style>

        {{-- Tarjeta principal --}}
        <div class="relative match-card max-w-2xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden ring-1 ring-white/20"
             wire:click.stop>

            {{-- Cabecera con dos avatares --}}
            <div class="relative h-44 sm:h-52 bg-gradient-to-br from-brand-500 via-brand-400 to-sage-400 overflow-hidden">
                <div class="absolute inset-0 flex items-center justify-center gap-6 sm:gap-8">
                    {{-- Yo --}}
                    <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-full overflow-hidden ring-4 ring-white shadow-xl bg-cream-100 flex items-center justify-center">
                        @if($yo->avatar_photo)
                            <img src="{{ $yo->avatar_photo }}" class="w-full h-full object-cover" alt="">
                        @else
                            <span class="text-3xl font-bold text-brand-700">{{ $yo->getIniciales() }}</span>
                        @endif
                    </div>

                    {{-- Corazón central --}}
                    <div class="relative">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-white flex items-center justify-center shadow-2xl">
                            <span class="text-3xl sm:text-4xl">🐾</span>
                        </div>
                    </div>

                    {{-- El otro --}}
                    <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-full overflow-hidden ring-4 ring-white shadow-xl bg-cream-100 flex items-center justify-center">
                        @if($otro->avatar_photo)
                            <img src="{{ $otro->avatar_photo }}" class="w-full h-full object-cover" alt="">
                        @else
                            <span class="text-3xl font-bold text-brand-700">{{ $otro->getIniciales() }}</span>
                        @endif
                    </div>
                </div>

                {{-- Botón cerrar --}}
                <button type="button" wire:click="cerrar"
                        class="absolute top-3 right-3 w-9 h-9 rounded-full bg-white/85 hover:bg-white text-ink-800 flex items-center justify-center font-bold text-lg shadow"
                        aria-label="Cerrar">✕</button>
            </div>

            {{-- Cuerpo --}}
            <div class="px-6 sm:px-8 py-6 text-center">
                <p class="text-[11px] uppercase tracking-[0.25em] font-bold text-brand-500">¡Es un PawMatch!</p>
                <h2 class="h-display text-3xl sm:text-4xl text-ink-900 mt-1">
                    Tus peludos y los de {{ explode(' ', $otro->name)[0] }} se han gustado
                    @if($otro->es_premium) <x-premium-badge size="md" /> @endif
                </h2>
                <p class="text-sm text-ink-700/65 mt-2 leading-relaxed">
                    Ahora tu y {{ explode(' ', $otro->name)[0] }} podeis hablar. Estos son los perros que entran en el match:
                </p>

                {{-- Perros de ambos lados --}}
                <div class="mt-5 grid grid-cols-2 gap-4 text-left">
                    <div class="bg-cream-50 rounded-2xl p-4 ring-1 ring-cream-200">
                        <p class="text-[11px] font-bold text-ink-700/45 uppercase tracking-widest mb-2">
                            Tus perros ({{ $misPerros->count() }})
                        </p>
                        <div class="space-y-2">
                            @forelse($misPerros as $p)
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $p->foto_url }}" alt="{{ $p->nombre }}" class="w-9 h-9 rounded-xl object-cover ring-1 ring-cream-200" onerror="this.onerror=null; this.src='{{ $p->placeholder_url }}'">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-ink-800 truncate">{{ $p->nombre }}</p>
                                        <p class="text-[11px] text-ink-700/55 truncate">{{ $p->raza ?: '—' }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-ink-700/45">Aún no has registrado ningún perro.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-cream-50 rounded-2xl p-4 ring-1 ring-cream-200">
                        <p class="text-[11px] font-bold text-ink-700/45 uppercase tracking-widest mb-2">
                            Sus perros ({{ $susPerros->count() }})
                        </p>
                        <div class="space-y-2">
                            @forelse($susPerros as $p)
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $p->foto_url }}" alt="{{ $p->nombre }}" class="w-9 h-9 rounded-xl object-cover ring-1 ring-cream-200" onerror="this.onerror=null; this.src='{{ $p->placeholder_url }}'">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-ink-800 truncate">{{ $p->nombre }}</p>
                                        <p class="text-[11px] text-ink-700/55 truncate">{{ $p->raza ?: '—' }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-ink-700/45">Aún no ha registrado perros.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ $conversacionId ? route('chat', ['conversacion' => $conversacionId]) : route('chat') }}"
                       wire:navigate
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold shadow-soft transition">
                        💬 Enviar mensaje
                    </a>
                    <button type="button" wire:click="cerrar"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-cream-100 ring-1 ring-cream-300 text-ink-800 text-sm font-semibold hover:bg-cream-200 transition">
                        Seguir descubriendo
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
</div>
