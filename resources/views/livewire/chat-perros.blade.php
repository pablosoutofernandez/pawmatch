<div class="w-full flex items-start">
    @include('partials.sidebar')

    <div class="flex-1 min-w-0 px-4 sm:px-6 lg:px-8 pt-6 pb-8">
      <div class="soft-card overflow-hidden flex min-h-[calc(100vh-140px)]">

        {{-- ═══ Lista de conversaciones ═══ --}}
        <div class="w-72 xl:w-80 border-r border-cream-200 flex flex-col flex-shrink-0 bg-cream-50/40">
            <div class="px-5 py-4 border-b border-cream-200">
                <h2 class="h-display text-xl">Mensajes</h2>
                <p class="text-[12px] text-ink-700/50 mt-0.5">{{ $conversaciones->count() }} conversaciones</p>
            </div>

            <div class="flex-1 overflow-y-auto p-2 space-y-1" wire:poll.6s>
                @forelse($conversaciones as $conv)
                <button wire:click="abrirConversacion({{ $conv->id }})"
                        class="w-full flex items-center gap-3 px-3 py-3 text-left rounded-2xl transition-colors
                               {{ $conversacionActiva === $conv->id ? 'bg-white shadow-soft ring-1 ring-brand-100' : 'hover:bg-white/70' }}">

                    <div class="relative flex-shrink-0">
                        @if($conv->avatar)
                        <img src="{{ $conv->avatar }}" alt="{{ $conv->nombre }}"
                             class="w-11 h-11 rounded-full object-cover shadow-sm">
                        @else
                        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-brand-300 to-sage-400 flex items-center justify-center text-sm font-bold text-white shadow-sm">
                            {{ $conv->iniciales }}
                        </div>
                        @endif
                        @if($conv->activa)
                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-sage-500 rounded-full border-2 border-white"></div>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline justify-between">
                            <span class="text-sm font-bold text-ink-800 truncate">{{ $conv->nombre }}</span>
                            <span class="text-[10px] text-ink-700/40 flex-shrink-0 ml-1">{{ $conv->hora }}</span>
                        </div>
                        <p class="text-xs text-ink-700/55 truncate mt-0.5">{{ $conv->ultimo }}</p>
                    </div>

                    @if($conv->no_leidos > 0)
                    <div class="w-5 h-5 bg-brand-500 rounded-full flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0">
                        {{ $conv->no_leidos }}
                    </div>
                    @endif
                </button>
                @empty
                <div class="px-4 py-10 text-center">
                    <div class="text-5xl mb-3">🐾</div>
                    <p class="text-sm font-bold text-ink-800">Aún no tienes matches</p>
                    <p class="text-xs text-ink-700/55 mt-1">Da like en <a href="{{ route('discover') }}" class="text-brand-500 font-bold">Descubrir</a>. Cuando os deis like mutuo, aparecerá aquí el chat.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- ═══ Ventana de chat ═══ --}}
        <div class="flex-1 flex flex-col bg-cream-50/20">

            @if($conversacionInfo)
            {{-- Header --}}
            <div class="bg-white/60 backdrop-blur border-b border-cream-200 px-5 py-3.5 flex items-center gap-3">
                @if($conversacionInfo->avatar)
                <img src="{{ $conversacionInfo->avatar }}" class="w-10 h-10 rounded-full object-cover">
                @else
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-300 to-sage-400 flex items-center justify-center text-sm font-bold text-white">
                    {{ $conversacionInfo->iniciales }}
                </div>
                @endif
                <div class="flex-1">
                    <p class="font-bold text-ink-800 text-sm">{{ $conversacionInfo->nombre }}</p>
                    <div class="flex items-center gap-1 text-[11px] {{ $conversacionInfo->activa ? 'text-sage-600' : 'text-ink-700/40' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $conversacionInfo->activa ? 'bg-sage-500' : 'bg-ink-300' }} inline-block"></span>
                        {{ $conversacionInfo->activa ? 'Paseando ahora' : 'Desconectado' }}
                    </div>
                </div>
            </div>

            {{-- Mensajes --}}
            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3"
                 id="chat-mensajes"
                 wire:poll.5s
                 x-data
                 x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
                 x-on:scroll-bottom.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)">

                <div class="text-center">
                    <span class="text-[11px] font-semibold text-ink-700/45 bg-cream-100 px-3 py-1 rounded-full">Conversación</span>
                </div>

                @forelse($mensajes as $msg)
                <div class="flex {{ $msg['out'] ? 'justify-end' : 'items-end gap-2' }}">
                    @if(!$msg['out'])
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-brand-300 to-sage-400 flex-shrink-0 flex items-center justify-center text-[10px] font-bold text-white mb-0.5">
                        {{ $conversacionInfo->iniciales }}
                    </div>
                    @endif
                    <div>
                        <div class="{{ $msg['out'] ? 'chat-out' : 'chat-in' }}">{{ $msg['cuerpo'] }}</div>
                        <p class="text-[9px] text-ink-700/40 mt-1 {{ $msg['out'] ? 'text-right' : '' }}">{{ $msg['hora'] }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-10 text-ink-700/45">
                    <div class="text-4xl mb-2">💬</div>
                    <p class="text-sm">¡Sois match! Escribe el primer mensaje.</p>
                </div>
                @endforelse
            </div>

            {{-- Input --}}
            <div class="bg-white/60 backdrop-blur border-t border-cream-200 px-4 py-3">
                <form wire:submit.prevent="enviar" class="flex items-center gap-2">
                    <input type="text" wire:model="nuevoMensaje" placeholder="Escribe un mensaje..."
                           class="flex-1 bg-cream-100 border-0 ring-1 ring-cream-300 rounded-full px-4 py-2.5 text-sm text-ink-800 placeholder:text-ink-700/40 focus:outline-none focus:ring-2 focus:ring-brand-300">
                    <button type="submit"
                            class="w-11 h-11 rounded-full flex items-center justify-center text-white bg-gradient-to-br from-brand-400 to-brand-600 hover:from-brand-500 hover:to-brand-700 shadow-soft transition-all flex-shrink-0 active:scale-95 disabled:opacity-50">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z"/>
                        </svg>
                    </button>
                </form>
                @error('nuevoMensaje')<p class="text-[11px] text-red-500 font-medium mt-1 ml-1">{{ $message }}</p>@enderror
            </div>

            @else
            <div class="flex-1 flex flex-col items-center justify-center text-ink-700/45 p-8">
                <div class="text-6xl mb-3 animate-float-slow">💬</div>
                <p class="h-display text-xl text-ink-800">Selecciona una conversación</p>
                <p class="text-sm mt-1">Tus matches aparecerán aquí</p>
            </div>
            @endif
        </div>
      </div>
    </div>
</div>
