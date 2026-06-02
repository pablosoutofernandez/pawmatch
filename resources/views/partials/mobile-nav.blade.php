{{-- Barra de navegación inferior (solo móvil). En escritorio se usa la
     barra lateral (partials/sidebar). Replica los mismos destinos. --}}
@auth
@php
    $u = auth()->user();
    $notifCount = $u->mensajesNoLeidos();
    if ($u->es_premium) {
        $notifCount += $u->notificaciones_count;
    }

    $navItems = [
        ['route' => 'dashboard',      'label' => 'Inicio',     'icon' => 'M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z', 'badge' => 0],
        ['route' => 'discover',       'label' => 'Descubrir',  'icon' => 'm14.5 14.5 5 5M16 10a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z', 'badge' => 0],
        ['route' => 'mapa',           'label' => 'Mapa',       'icon' => 'M9 4 3 6v14l6-2 6 2 6-2V4l-6 2-6-2Zm0 0v14m6-12v14', 'badge' => 0],
        ['route' => 'chat',           'label' => 'Chat',       'icon' => 'M4 5h16v11H8l-4 4V5Z', 'badge' => 0],
        ['route' => 'notificaciones', 'label' => 'Avisos',     'icon' => 'M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0', 'badge' => $notifCount],
        ['route' => 'mi-perfil',      'label' => 'Perfil',     'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0', 'badge' => 0],
    ];
@endphp

<nav class="md:hidden fixed bottom-0 inset-x-0 z-[1300] bg-cream-50/95 backdrop-blur-md border-t border-cream-200 shadow-[0_-4px_16px_rgba(0,0,0,0.06)]">
    <div class="flex items-stretch justify-around px-1 pb-[env(safe-area-inset-bottom)]">
        @foreach($navItems as $item)
            @php
                $active = request()->routeIs($item['route'])
                    || ($item['route'] === 'mi-perfil' && request()->routeIs('perfil'))
                    || ($item['route'] === 'discover' && (request()->routeIs('ver-perfil') || request()->routeIs('ver-perro')));
            @endphp
            <a href="{{ route($item['route']) }}"
               class="relative flex flex-col items-center justify-center gap-0.5 flex-1 py-2 transition-colors
                      {{ $active ? 'text-brand-600' : 'text-ink-700/55 hover:text-ink-900' }}">
                <span class="relative">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                    @if($item['badge'] > 0)
                        <span class="absolute -top-1.5 -right-2 min-w-[16px] h-[16px] px-1 bg-brand-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center ring-2 ring-cream-50">
                            {{ $item['badge'] > 9 ? '9+' : $item['badge'] }}
                        </span>
                    @endif
                </span>
                <span class="text-[10px] font-semibold leading-none">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
@endauth
