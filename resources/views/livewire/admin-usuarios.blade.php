<div class="w-full flex items-start">
    @include('partials.sidebar')

    <main class="flex-1 min-w-0 px-4 md:px-8 py-6 max-w-6xl">
        <header class="mb-6">
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-700 text-[10px] font-bold uppercase tracking-widest">Admin</span>
                <p class="text-[11px] text-slate-500 uppercase tracking-widest font-semibold">Panel de administración</p>
            </div>
            <h1 class="h-display text-3xl text-ink-800">Usuarios</h1>
            <p class="text-sm text-ink-700/70 mt-1">Lista de todas las cuentas registradas. Puedes buscar y eliminar usuarios (excepto otros administradores y tú mismo).</p>
        </header>

        {{-- Flash --}}
        @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-semibold ring-1 ring-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-700 text-sm font-semibold ring-1 ring-rose-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- Buscador --}}
        <div class="mb-4">
            <input type="search" wire:model.live.debounce.300ms="buscar"
                   placeholder="Buscar por nombre o email…"
                   class="w-full md:w-96 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-400">
        </div>

        {{-- Tabla --}}
        <div class="bg-white rounded-2xl ring-1 ring-slate-100 shadow-soft overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-[10px] uppercase tracking-widest text-slate-500 font-bold">
                        <tr>
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Usuario</th>
                            <th class="px-4 py-3 text-left">Plan</th>
                            <th class="px-4 py-3 text-center">Perros</th>
                            <th class="px-4 py-3 text-center">Likes ↑↓</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($usuarios as $u)
                            @php
                                $esAdmin = $u->roles->contains('name', 'admin');
                                $esYo    = $u->id === auth()->id();
                                $bloqueado = $esAdmin || $esYo;
                            @endphp
                            <tr wire:key="user-{{ $u->id }}" class="hover:bg-slate-50/60">
                                <td class="px-4 py-3 text-slate-400 font-mono text-xs">{{ $u->id }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-100 to-cream-200 flex items-center justify-center overflow-hidden text-xs font-bold text-brand-700">
                                            @if($u->avatar_photo)
                                                <img src="{{ $u->avatar_photo }}" alt="{{ $u->name }}" class="w-full h-full object-cover">
                                            @else
                                                {{ $u->getIniciales() }}
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-ink-800 truncate flex items-center gap-1.5">
                                                {{ $u->name }}
                                                @if($esAdmin)
                                                    <span class="px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 text-[9px] font-bold uppercase tracking-wider">Admin</span>
                                                @endif
                                                @if($esYo)
                                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[9px] font-bold uppercase tracking-wider">Tú</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-slate-500 truncate">{{ $u->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($u->es_premium)
                                        <span class="text-[11px] font-bold text-amber-700">★ Premium</span>
                                    @else
                                        <span class="text-[11px] text-slate-500">Gratuito</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-slate-700 font-semibold">{{ $u->perros_count }}</td>
                                <td class="px-4 py-3 text-center text-xs text-slate-500">
                                    {{ $u->likes_enviados_count }} / {{ $u->likes_recibidos_count }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($confirmandoId === $u->id)
                                        <div class="inline-flex items-center gap-2">
                                            <span class="text-[11px] font-semibold text-rose-600">¿Seguro?</span>
                                            <button wire:click="eliminar({{ $u->id }})"
                                                    class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition">
                                                Sí, eliminar
                                            </button>
                                            <button wire:click="cancelarConfirmacion"
                                                    class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                                Cancelar
                                            </button>
                                        </div>
                                    @else
                                        <button wire:click="pedirConfirmacion({{ $u->id }})"
                                                @disabled($bloqueado)
                                                title="{{ $bloqueado ? ($esYo ? 'No puedes eliminarte a ti mismo' : 'No puedes eliminar a otro administrador') : 'Eliminar este usuario' }}"
                                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition
                                                       {{ $bloqueado
                                                            ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                                                            : 'bg-rose-50 hover:bg-rose-100 text-rose-700 ring-1 ring-rose-200' }}">
                                            🗑 Eliminar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">
                                    No se han encontrado usuarios.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $usuarios->links() }}
            </div>
        </div>

        <p class="text-[11px] text-slate-400 mt-4">
            Eliminar un usuario borra permanentemente sus perros, likes, mensajes y conversaciones.
        </p>
    </main>
</div>
