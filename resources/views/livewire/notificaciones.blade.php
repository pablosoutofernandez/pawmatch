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

            {{-- Header --}}
            <div class="animate-fade-up">
                <h1 class="h-display text-4xl sm:text-5xl leading-none">Notificaciones</h1>
                <p class="text-ink-700/60 mt-2 text-[15px]">Personas a las que les gusta tu perfil.</p>
            </div>

            {{-- Likes pendientes --}}
            <div class="space-y-3">
                <p class="text-[11px] font-bold text-ink-700/45 uppercase tracking-widest">
                    Nuevos likes ({{ $pendientes->count() }})
                </p>

                @forelse($pendientes as $like)
                <div class="soft-card p-4 flex items-center gap-4 animate-pop-in" wire:key="like-{{ $like->id }}">
                    {{-- Avatar --}}
                    <a href="{{ route('ver-perfil', $like->de_user_id) }}" class="flex-shrink-0">
                        @if($like->deUsuario?->avatar_photo)
                        <img src="{{ $like->deUsuario->avatar_photo }}" class="w-14 h-14 rounded-full object-cover ring-2 ring-brand-100">
                        @else
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-brand-300 to-brand-500 flex items-center justify-center text-lg font-bold text-white">
                            {{ strtoupper(substr($like->deUsuario?->name ?? '?', 0, 1)) }}
                        </div>
                        @endif
                    </a>

                    {{-- Texto --}}
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

                    {{-- Acciones --}}
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
                <div class="soft-card-flat text-center py-14">
                    <div class="text-5xl mb-3 animate-float-slow inline-block">🔔</div>
                    <p class="h-display text-xl text-ink-800">Sin notificaciones nuevas</p>
                    <p class="text-sm text-ink-700/55 mt-1">Cuando alguien le dé like a tu perfil, aparecerá aquí.</p>
                </div>
                @endforelse
            </div>

            {{-- Matches --}}
            @if($matches->isNotEmpty())
            <div class="space-y-3 pt-2">
                <p class="text-[11px] font-bold text-ink-700/45 uppercase tracking-widest">Tus matches</p>
                <div class="flex flex-wrap gap-3">
                    @foreach($matches as $m)
                    <a href="{{ route('ver-perfil', $m->de_user_id) }}" class="soft-card-flat px-3 py-2 flex items-center gap-2.5 hover:shadow-soft transition-all" wire:key="match-{{ $m->id }}">
                        @if($m->deUsuario?->avatar_photo)
                        <img src="{{ $m->deUsuario->avatar_photo }}" class="w-8 h-8 rounded-full object-cover">
                        @else
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-sage-300 to-sage-500 flex items-center justify-center text-xs font-bold text-white">
                            {{ strtoupper(substr($m->deUsuario?->name ?? '?', 0, 1)) }}
                        </div>
                        @endif
                        <span class="text-sm font-semibold text-ink-800">{{ explode(' ', $m->deUsuario?->name ?? '?')[0] }}</span>
                        <span class="text-brand-500">→</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
