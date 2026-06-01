<div>
    {{-- Modal overlay y contenido --}}
    <div x-show="{{ $abierto ? 'true' : 'false' }}"
         x-transition.opacity
         class="fixed inset-0 z-50 overflow-y-auto px-4 py-6"
         style="display: {{ $abierto ? 'block' : 'none' }};">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"
             wire:click="cerrar"></div>

        {{-- Modal content --}}
        <div class="relative min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden">

                {{-- Header con gradiente --}}
                <div class="bg-gradient-to-r from-amber-400 via-orange-500 to-red-500 px-6 py-8 text-center">
                    <div class="text-6xl mb-4">🔒</div>
                    <h2 class="text-2xl font-bold text-white mb-2">
                        ¡Límite Alcanzado!
                    </h2>
                    <p class="text-white/90 text-sm">
                        Has llegado al máximo de matches gratuitos
                    </p>
                </div>

                {{-- Contenido --}}
                <div class="px-6 py-6 space-y-4">
                    <div class="text-center space-y-3">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 rounded-full border border-amber-200">
                            <span class="text-2xl">💕</span>
                            <span class="font-semibold text-amber-700">
                                {{ $matchesActivos }} / {{ $limiteMatches }} matches activos
                            </span>
                        </div>

                        <p class="text-ink-700/70 text-sm leading-relaxed">
                            Con el <strong>plan gratuito</strong> puedes tener hasta {{ $limiteMatches }} matches simultáneos.
                            Para dar más likes, necesitas espacio libre o hacerte Premium.
                        </p>
                    </div>

                    {{-- Opciones --}}
                    <div class="space-y-3 pt-2">
                        {{-- Opción Premium --}}
                        <a href="{{ route('premium') }}"
                           class="block w-full bg-gradient-to-r from-brand-500 to-brand-600 text-white text-center font-bold py-3.5 px-4 rounded-xl hover:from-brand-600 hover:to-brand-700 transition-all transform hover:scale-[1.02] shadow-lg">
                            ⭐ Hazte Premium - Matches Ilimitados
                        </a>

                        {{-- Gestionar matches existentes --}}
                        <a href="{{ route('conversaciones.index') }}"
                           class="block w-full bg-cream-100 text-ink-700 text-center font-semibold py-3 px-4 rounded-xl hover:bg-cream-200 transition-colors border border-cream-300">
                            📱 Gestionar Conversaciones Actuales
                        </a>
                    </div>

                    {{-- Tip --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mt-4">
                        <div class="flex items-start gap-3">
                            <span class="text-blue-500 text-lg">💡</span>
                            <div>
                                <p class="text-blue-800 font-semibold text-sm mb-1">¿Sabías que...?</p>
                                <p class="text-blue-700 text-xs leading-relaxed">
                                    Si una conversación no prospera, puedes cerrarla para liberar espacio para nuevos matches.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 border-t">
                    <button wire:click="cerrar"
                            class="w-full py-2.5 text-ink-700/60 hover:text-ink-700 font-medium text-sm transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>