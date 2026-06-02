<aside class="hidden md:block w-60 flex-shrink-0 min-h-screen px-3 pt-6">
    @php
        $notifCount = 0;
        if (auth()->check()) {
            $u = auth()->user();
            // Mensajes nuevos: para todos. Likes recibidos: solo premium
            // (los gratuitos no ven quién les da like).
            $notifCount = $u->mensajesNoLeidos();
            if ($u->es_premium) {
                $notifCount += $u->notificaciones_count;
            }
        }
    @endphp
    <nav class="flex flex-col gap-1.5 sticky top-24">
        @php
            $items = [
                ['route' => 'dashboard',     'label' => 'Inicio',         'icon' => 'M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z', 'badge' => 0],
                ['route' => 'discover',      'label' => 'Descubrir',      'icon' => 'm14.5 14.5 5 5M16 10a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z', 'badge' => 0],
                ['route' => 'notificaciones','label' => 'Notificaciones', 'icon' => 'M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0', 'badge' => $notifCount],
                ['route' => 'chat',          'label' => 'Chat',           'icon' => 'M4 5h16v11H8l-4 4V5Z', 'badge' => 0],
                ['route' => 'mapa',          'label' => 'Mapa',           'icon' => 'M9 4 3 6v14l6-2 6 2 6-2V4l-6 2-6-2Zm0 0v14m6-12v14', 'badge' => 0],
                ['route' => 'mi-perfil',     'label' => 'Mi perfil',      'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0', 'badge' => 0],
                ['route' => 'premium',       'label' => 'Premium',        'icon' => 'M5 16 3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5Zm0 3h14', 'badge' => 0],
            ];
        @endphp

        @foreach($items as $item)
            @php
        $active = request()->routeIs($item['route'])
                    || ($item['route'] === 'mi-perfil' && request()->routeIs('perfil'))
                    || ($item['route'] === 'discover' && request()->routeIs('ver-perfil'))
                    || ($item['route'] === 'discover' && request()->routeIs('ver-perro'));
            @endphp
            <a href="{{ route($item['route']) }}"
               class="group flex items-center gap-3 px-4 py-2.5 rounded-2xl text-sm font-semibold transition-all
                      {{ $active
                            ? 'bg-white text-brand-600 shadow-soft ring-1 ring-brand-100'
                            : 'text-ink-700/70 hover:text-ink-900 hover:bg-white/60' }}">
                <span class="relative flex items-center justify-center w-9 h-9 rounded-xl transition-colors
                             {{ $active ? 'bg-brand-100 text-brand-600' : 'bg-cream-100 text-ink-700/50 group-hover:bg-cream-200' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                    @if($item['badge'] > 0)
                    <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-brand-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center ring-2 ring-cream-50">
                        {{ $item['badge'] > 9 ? '9+' : $item['badge'] }}
                    </span>
                    @endif
                </span>
                {{ $item['label'] }}
            </a>
        @endforeach

        {{-- Estado del plan / CTA premium --}}
        @auth
            {{-- Acceso al panel de admin (solo para usuarios con rol admin) --}}
            @if(auth()->user()->hasRole('admin'))
                @php $adminActive = request()->routeIs('admin.*'); @endphp
                <a href="{{ route('admin.usuarios') }}"
                   class="group flex items-center gap-3 px-4 py-2.5 rounded-2xl text-sm font-semibold transition-all
                          {{ $adminActive
                                ? 'bg-rose-50 text-rose-700 ring-1 ring-rose-200'
                                : 'text-ink-700/70 hover:text-rose-700 hover:bg-rose-50/60' }}">
                    <span class="relative flex items-center justify-center w-9 h-9 rounded-xl transition-colors
                                 {{ $adminActive ? 'bg-rose-100 text-rose-700' : 'bg-cream-100 text-ink-700/50 group-hover:bg-rose-100 group-hover:text-rose-600' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 3 4 6v6c0 5 3.5 8.5 8 9 4.5-.5 8-4 8-9V6l-8-3Z"/>
                        </svg>
                    </span>
                    Admin
                </a>
            @endif

            @php $restantes = auth()->user()->matchesRestantes(); @endphp
            @if($restantes === null)
                <div class="mt-3 px-4 py-3 rounded-2xl bg-brand-50 ring-1 ring-brand-100 text-brand-600 text-xs font-semibold flex items-center gap-2">
                    <span>★</span> Plan Premium activo
                </div>
            @else
                <a href="{{ route('premium') }}"
                   class="mt-3 block px-4 py-3 rounded-2xl bg-cream-50 ring-1 ring-cream-300 hover:ring-brand-200 transition">
                    <p class="text-xs text-ink-700/60">Plan gratuito</p>
                    <p class="text-sm font-semibold text-ink-900">
                        Te {{ $restantes === 1 ? 'queda' : 'quedan' }} {{ $restantes }}
                        {{ $restantes === 1 ? 'match' : 'matches' }}
                    </p>
                    <p class="text-xs text-brand-600 font-semibold mt-1">Hazte Premium →</p>
                </a>
            @endif
        @endauth
    </nav>
</aside>
