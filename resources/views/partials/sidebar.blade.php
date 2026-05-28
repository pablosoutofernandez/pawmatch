<aside class="hidden md:block w-60 flex-shrink-0 min-h-screen px-3 pt-6">
    <nav class="flex flex-col gap-1.5 sticky top-24">
        @php
            $items = [
                ['route' => 'dashboard', 'label' => 'Inicio',    'icon' => 'M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z'],
                ['route' => 'discover',  'label' => 'Descubrir', 'icon' => 'm14.5 14.5 5 5M16 10a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z'],
                ['route' => 'chat',      'label' => 'Chat',      'icon' => 'M4 5h16v11H8l-4 4V5Z'],
                ['route' => 'mapa',      'label' => 'Mapa',      'icon' => 'M9 4 3 6v14l6-2 6 2 6-2V4l-6 2-6-2Zm0 0v14m6-12v14'],
                ['route' => 'mi-perfil', 'label' => 'Mi perfil', 'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0'],
            ];
        @endphp

        @foreach($items as $item)
            @php
                $active = request()->routeIs($item['route'])
                    || ($item['route'] === 'mi-perfil' && request()->routeIs('perfil', 'ver-perfil'));
            @endphp
            <a href="{{ route($item['route']) }}"
               class="group flex items-center gap-3 px-4 py-2.5 rounded-2xl text-sm font-semibold transition-all
                      {{ $active
                            ? 'bg-white text-brand-600 shadow-soft ring-1 ring-brand-100'
                            : 'text-ink-700/70 hover:text-ink-900 hover:bg-white/60' }}">
                <span class="flex items-center justify-center w-9 h-9 rounded-xl transition-colors
                             {{ $active ? 'bg-brand-100 text-brand-600' : 'bg-cream-100 text-ink-700/50 group-hover:bg-cream-200' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                </span>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</aside>
