<div class="col-span-12 md:col-span-1 hidden md:block bg-white border-r border-slate-100 min-h-screen">
    <p class="text-[14px] font-bold uppercase tracking-widest text-slate-500 px-6 py-4">
        Menu
    </p>

    <nav class="flex flex-col">
        <a href="{{ route('dashboard') }}"
           class="flex items-center text-xs font-bold uppercase tracking-widest px-6 py-3 transition-colors
                  {{ request()->routeIs('dashboard')
                        ? 'text-brand-600 bg-brand-50 border-l-4 border-brand-500'
                        : 'text-slate-700 hover:text-slate-900 hover:bg-slate-50' }}">
            Dashboard
        </a>

        <a href="{{ route('discover') }}"
           class="flex items-center text-xs font-bold uppercase tracking-widest px-6 py-3 transition-colors
                  {{ request()->routeIs('discover')
                        ? 'text-brand-600 bg-brand-50 border-l-4 border-brand-500'
                        : 'text-slate-700 hover:text-slate-900 hover:bg-slate-50' }}">
            Discover
        </a>

        <a href="{{ route('chat') }}"
           class="flex items-center text-xs font-bold uppercase tracking-widest px-6 py-3 transition-colors
                  {{ request()->routeIs('chat')
                        ? 'text-brand-600 bg-brand-50 border-l-4 border-brand-500'
                        : 'text-slate-700 hover:text-slate-900 hover:bg-slate-50' }}">
            Chat
        </a>

        <a href="{{ route('mapa') }}"
           class="flex items-center text-xs font-bold uppercase tracking-widest px-6 py-3 transition-colors
                  {{ request()->routeIs('mapa')
                        ? 'text-brand-600 bg-brand-50 border-l-4 border-brand-500'
                        : 'text-slate-700 hover:text-slate-900 hover:bg-slate-50' }}">
            Mapa
        </a>

        <a href="{{ route('perfil') }}"
           class="flex items-center text-xs font-bold uppercase tracking-widest px-6 py-3 transition-colors
                  {{ request()->routeIs('perfil')
                        ? 'text-brand-600 bg-brand-50 border-l-4 border-brand-500'
                        : 'text-slate-700 hover:text-slate-900 hover:bg-slate-50' }}">
            Mi Perfil
        </a>
    </nav>

    {{-- Plan card --}}
    @if(auth()->user()->plan === 'free')
    <div class="mx-4 mt-6 rounded-2xl p-4 text-white bg-gradient-to-br from-brand-500 to-brand-700">
        <p class="font-bold text-sm">✨ Prueba Premium</p>
        <p class="text-[10px] mt-1 opacity-80 leading-tight">Matches ilimitados, sin anuncios</p>
        <button class="mt-2 text-[11px] font-bold bg-white text-brand-600 rounded-lg px-3 py-1 hover:bg-brand-50">
            4,99 €/mes
        </button>
    </div>
    @endif
</div>
