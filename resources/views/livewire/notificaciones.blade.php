<div class="w-full flex items-start">
    @include('partials.sidebar')

    <div class="flex-1 min-w-0 min-h-screen pt-6 md:pt-10 px-4 sm:px-6 lg:px-10 pb-16">
        <div class="w-full max-w-3xl mx-auto space-y-7">

            {{-- Flash de match --}}
            @if(session('match'))
                <div class="flex items-center gap-3 bg-sage-50 ring-1 ring-sage-200 text-sage-700 px-5 py-3.5 rounded-2xl shadow-soft animate-fade-in-down">
                    <span class="text-lg">🎉</span>
                    <span class="text-sm font-semibold">{{ session('match') }}</span>
                    <a href="{{ route('chat') }}" class="ml-auto text-sm font-bold text-brand-600 hover:underline">Ir al chat →</a>
                </div>
            @endif
            @if(session('premium'))
                <div class="flex items-center justify-between gap-3 bg-brand-50 ring-1 ring-brand-100 text-brand-700 px-5 py-3.5 rounded-2xl shadow-soft animate-fade-in-down">
                    <span class="text-sm font-semibold">⭐ {{ session('premium') }}</span>
                    <a href="{{ route('premium') }}" class="shrink-0 px-4 py-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold transition">Hazte Premium</a>
                </div>
            @endif

            {{-- Header --}}
            <div class="animate-fade-up">
                <h1 class="h-display text-4xl sm:text-5xl leading-none">Notificaciones</h1>
                <p class="text-ink-700/60 mt-2 text-[15px]">
                    @if($esPremium)
                        Mensajes nuevos y quién le ha dado like a tu perfil.
                    @else
                        Tus mensajes nuevos. ✨ Hazte Premium para ver quién te da like.
                    @endif
                </p>
            </div>

            {{-- ════════ MENSAJES NUEVOS (todos los usuarios) ════════ --}}
            <div class="space-y-3">
                <p class="text-[11px] font-bold text-ink-700/45 uppercase tracking-widest">
                    Mensajes nuevos ({{ $mensajesNuevos->sum('no_leidos') }})
                </p>

                @forelse($mensajesNuevos as $n)
                    <a href="{{ route('chat', ['conversacion' => $n->conversacion_id]) }}"
                       class="soft-card p-4 flex items-center gap-4 animate-pop-in hover:shadow-soft transition-all"
                       wire:key="msg-{{ $n->conversacion_id }}">
                        {{-- Avatar del otro --}}
                        <div class="relative flex-shrink-0">
                            @if($n->otro?->avatar_photo)
                                <img src="{{ $n->otro->avatar_photo }}" class="w-14 h-14 rounded-full object-cover ring-2 ring-sage-200">
                            @else
                                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-sage-300 to-sage-500 flex items-center justify-center text-lg font-bold text-white">
                                    {{ strtoupper(substr($n->otro?->name ?? '?', 0, 1)) }}
                                </div>
                            @endif
                            <span class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 bg-brand-500 text-white text-[11px] font-bold rounded-full flex items-center justify-center ring-2 ring-white">
                                {{ $n->no_leidos > 9 ? '9+' : $n->no_leidos }}
                            </span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-ink-800">
                                <span class="font-bold">{{ $n->otro?->name ?? 'Alguien' }}</span>
                                te ha escrito
                            </p>
                            <p class="text-xs text-ink-700/55 mt-0.5 truncate">
                                @if($n->ultimo) “{{ \Illuminate\Support\Str::limit($n->ultimo, 60) }}” @endif
                                @if($n->hora) · {{ $n->hora->diffForHumans() }} @endif
                            </p>
                        </div>

                        <span class="text-brand-500 flex-shrink-0">→</span>
                    </a>
                @empty
                    <div class="soft-card-flat text-center py-14">
                        <div class="text-5xl mb-3 animate-float-slow inline-block">💬</div>
                        <p class="h-display text-xl text-ink-800">Sin mensajes nuevos</p>
                        <p class="text-sm text-ink-700/55 mt-1">Cuando alguien de tus matches te escriba, lo verás aquí.</p>
                    </div>
                @endforelse
            </div>

            {{-- ════════ LIKES RECIBIDOS ════════ --}}
            @if($esPremium)
                {{-- Premium: ve el detalle de quién le ha dado like --}}
                <div class="space-y-3 pt-2">
                    <p class="text-[11px] font-bold text-ink-700/45 uppercase tracking-widest">
                        Nuevos likes ({{ $pendientes->count() }})
                    </p>

                    @forelse($pendientes as $like)
                    <div class="soft-card p-4 flex items-center gap-4 animate-pop-in" wire:key="like-{{ $like->id }}">
                        <a href="{{ route('ver-perfil', $like->de_user_id) }}" class="flex-shrink-0">
                            @if($like->deUsuario?->avatar_photo)
                            <img src="{{ $like->deUsuario->avatar_photo }}" class="w-14 h-14 rounded-full object-cover ring-2 ring-brand-100">
                            @else
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-brand-300 to-brand-500 flex items-center justify-center text-lg font-bold text-white">
                                {{ strtoupper(substr($like->deUsuario?->name ?? '?', 0, 1)) }}
                            </div>
                            @endif
                        </a>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-ink-800">
                                <a href="{{ route('ver-perfil', $like->de_user_id) }}" class="font-bold hover:text-brand-600">{{ $like->deUsuario?->name ?? 'Alguien' }}</a>
                                le ha dado like a
                                @if($like->aPerro)
                                    <a href="{{ route('ver-perro', $like->aPerro->id) }}" class="font-bold text-brand-600 hover:underline">{{ $like->aPerro->nombre }}</a>
                                @else
                                    <span class="font-bold">tu perfil</span>
                                @endif
                            </p>
                            <p class="text-xs text-ink-700/50 mt-0.5">
                                @if($like->dePerro) con {{ $like->dePerro->nombre }} · {{ $like->dePerro->raza }} @endif
                                · {{ $like->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button wire:click="ignorar({{ $like->id }})"
                                    class="w-9 h-9 rounded-full bg-cream-100 ring-1 ring-cream-300 text-ink-700/50 hover:text-ink-800 transition-colors flex items-center justify-center"
                                    title="Ignorar">✕</button>
                            <button wire:click="corresponder({{ $like->id }})"
                                    class="btn-primary !py-2 !px-4 text-sm">
                                ♥ Like de vuelta
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="soft-card-flat text-center py-10">
                        <div class="text-4xl mb-2">🔔</div>
                        <p class="text-sm text-ink-700/55">Nadie nuevo te ha dado like por ahora.</p>
                    </div>
                    @endforelse
                </div>
            @elseif($numLikesOcultos > 0)
                {{-- Gratuito: no se revela quién, solo el gancho premium --}}
                <div class="space-y-3 pt-2">
                    <p class="text-[11px] font-bold text-ink-700/45 uppercase tracking-widest">Likes recibidos</p>
                    <div class="soft-card p-5 ring-2 ring-brand-200 bg-gradient-to-r from-brand-50 to-cream-50 animate-pop-in">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-brand-500 flex items-center justify-center text-white text-xl">♥</div>
                            <div class="flex-1 min-w-0">
                                <p class="h-display text-lg text-ink-800 leading-tight">
                                    Tienes {{ $numLikesOcultos }} {{ $numLikesOcultos === 1 ? 'like esperando' : 'likes esperando' }}
                                </p>
                                <p class="text-sm text-ink-700/60 mt-0.5">Hazte Premium para ver quién le ha dado like a tu perfil y corresponder.</p>
                            </div>
                            <a href="{{ route('premium') }}" class="shrink-0 px-4 py-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold transition">Hazte Premium</a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ════════ MATCHES ════════ --}}
            @if($matches->isNotEmpty())
            <div class="space-y-3 pt-2">
                <p class="text-[11px] font-bold text-ink-700/45 uppercase tracking-widest">Tus matches ({{ $matches->count() }})</p>
                <div class="flex flex-wrap gap-3">
                    @foreach($matches as $m)
                        @php($perro = $m->aPerro)
                        @php($dueno = $perro?->dueno ?? $m->aUsuario)
                        @if($perro)
                        <a href="{{ route('ver-perro', $perro->id) }}"
                           class="soft-card-flat px-3 py-2 flex items-center gap-2.5 hover:shadow-soft transition-all"
                           wire:key="match-{{ $m->id }}"
                           title="{{ $perro->nombre }} — {{ $dueno?->name }}">
                            @if($perro->foto_principal)
                                <img src="{{ $perro->foto_principal_url }}" alt="{{ $perro->nombre }}"
                                     class="w-9 h-9 rounded-xl object-cover ring-1 ring-cream-200">
                            @else
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-200 to-sage-200 flex items-center justify-center text-base">🐶</div>
                            @endif
                            <div class="flex flex-col min-w-0">
                                <span class="text-sm font-bold text-ink-800 leading-tight">{{ $perro->nombre }}</span>
                                <span class="text-[11px] text-ink-700/55 leading-tight truncate">{{ explode(' ', $dueno?->name ?? '?')[0] }}</span>
                            </div>
                            <span class="text-brand-500 ml-1">→</span>
                        </a>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
