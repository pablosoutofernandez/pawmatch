<x-guest-layout>
    {{-- Session Status --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-7">
        <h1 class="h-display text-3xl">¡Hola de nuevo! 👋</h1>
        <p class="text-ink-700/60 mt-1.5 text-[15px]">Inicia sesión para volver con tu manada.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="field-label">Email</label>
            <input id="email" type="email" name="email" :value="old('email')" value="{{ old('email') }}"
                   required autofocus autocomplete="username" placeholder="hola@ejemplo.com" class="field">
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <label for="password" class="field-label">Contraseña</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="field">
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember"
                       class="rounded-md text-brand-500 focus:ring-brand-400 border-cream-300">
                <span class="text-sm text-ink-700/70">Recuérdame</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-brand-500 hover:text-brand-600" href="{{ route('password.request') }}">
                    ¿Olvidaste la contraseña?
                </a>
            @endif
        </div>

        <button type="submit" class="btn-primary w-full !py-3 mt-2">Iniciar sesión</button>
    </form>

    <p class="text-center text-sm text-ink-700/60 mt-6">
        ¿Nuevo por aquí?
        <a href="{{ route('register') }}" class="font-bold text-brand-500 hover:text-brand-600">Crea tu cuenta</a>
    </p>
</x-guest-layout>
