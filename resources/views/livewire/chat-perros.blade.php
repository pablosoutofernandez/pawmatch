<div class="w-full grid grid-cols-12 justify-start items-start gap-4">
    @include('partials.sidebar')

    <div class="w-full col-span-12 md:col-span-11 min-h-[calc(100vh-64px)] flex bg-white">

        {{-- ═══ Lista de conversaciones ═══ --}}
        <div class="w-72 xl:w-80 border-r border-slate-100 flex flex-col flex-shrink-0">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-800 text-lg">Mensajes</h2>
                <p class="text-[11px] text-slate-500 uppercase tracking-widest font-semibold mt-0.5">
                    {{ $conversaciones->count() }} conversaciones
                </p>
            </div>

            <div class="flex-1 overflow-y-auto">
                @foreach($conversaciones as $conv)
                <button wire:click="abrirConversacion({{ $conv->id }})"
                        class="w-full flex items-center gap-3 px-4 py-3.5 text-left border-b border-slate-50 transition-colors
                               {{ $conversacionActiva === $conv->id
                                    ? 'bg-brand-50 border-r-2 border-r-brand-500'
                                    : 'hover:bg-slate-50' }}">

                    <div class="relative flex-shrink-0">
                        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center text-sm font-bold text-slate-600">
                            {{ $conv->iniciales }}
                        </div>
                        @if($conv->activa)
                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white"></div>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline justify-between">
                            <span class="text-sm font-bold text-slate-800 truncate">{{ $conv->nombre }}</span>
                            <span class="text-[10px] text-slate-400 flex-shrink-0 ml-1">{{ $conv->hora }}</span>
                        </div>
                        <p class="text-xs text-slate-500 truncate mt-0.5">{{ $conv->ultimo }}</p>
                    </div>

                    @if($conv->no_leidos > 0)
                    <div class="w-5 h-5 bg-brand-500 rounded-full flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0">
                        {{ $conv->no_leidos }}
                    </div>
                    @endif
                </button>
                @endforeach
            </div>
        </div>

        {{-- ═══ Ventana de chat ═══ --}}
        <div class="flex-1 flex flex-col bg-slate-50/50">

            @if($conversacionInfo)
            {{-- Header --}}
            <div class="bg-white border-b border-slate-100 px-5 py-3.5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center text-sm font-bold text-slate-600">
                    {{ $conversacionInfo->iniciales }}
                </div>
                <div class="flex-1">
                    <p class="font-bold text-slate-800 text-sm">{{ $conversacionInfo->nombre }}</p>
                    <div class="flex items-center gap-1 text-[11px] text-emerald-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                        En línea
                    </div>
                </div>
                <button class="bg-brand-500 hover:bg-brand-600 text-white font-bold py-1.5 px-3 rounded-lg text-xs shadow-md transition-all">
                    📅 Proponer quedada
                </button>
            </div>

            {{-- Mensajes --}}
            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3"
                 id="chat-mensajes"
                 wire:poll.5s
                 x-data
                 x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
                 x-on:scroll-bottom.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)">

                <div class="text-center">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 bg-white px-3 py-1 rounded-full">Hoy</span>
                </div>

                @foreach($mensajes as $msg)
                <div class="flex {{ $msg['out'] ? 'justify-end' : 'items-end gap-2' }}">
                    @if(!$msg['out'])
                    <div class="w-7 h-7 rounded-full bg-slate-200 flex-shrink-0 flex items-center justify-center text-[10px] font-bold text-slate-600 mb-0.5">
                        {{ $conversacionInfo->iniciales }}
                    </div>
                    @endif
                    <div>
                        <div class="{{ $msg['out'] ? 'chat-out' : 'chat-in' }}">
                            {{ $msg['cuerpo'] }}
                        </div>
                        <p class="text-[9px] text-slate-400 mt-1 {{ $msg['out'] ? 'text-right' : '' }}">{{ $msg['hora'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Input --}}
            <div class="bg-white border-t border-slate-100 px-4 py-3">
                <form wire:submit.prevent="enviar" class="flex items-center gap-2">
                    <input type="text" wire:model="nuevoMensaje"
                           placeholder="Escribe un mensaje..."
                           class="flex-1 bg-slate-100 border-0 rounded-full px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-300">

                    <button type="submit"
                            @if(empty(trim($nuevoMensaje))) disabled @endif
                            class="w-10 h-10 rounded-full flex items-center justify-center text-white bg-brand-500 hover:bg-brand-600 transition-opacity flex-shrink-0 disabled:opacity-50">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z"/>
                        </svg>
                    </button>
                </form>
                @error('nuevoMensaje')<p class="text-[11px] text-red-500 font-semibold mt-1 ml-1">{{ $message }}</p>@enderror

                <p class="text-center text-[10px] text-slate-400 mt-2">
                    Plan gratuito: 10 mensajes/día ·
                    <a href="#" class="text-brand-500 font-bold">Premium: ilimitados</a>
                </p>
            </div>

            @else
            <div class="flex-1 flex flex-col items-center justify-center text-slate-400 p-8">
                <div class="text-6xl mb-3">💬</div>
                <p class="font-bold">Selecciona una conversación</p>
                <p class="text-sm mt-1">Tus matches aparecerán aquí</p>
            </div>
            @endif
        </div>
    </div>
</div>
