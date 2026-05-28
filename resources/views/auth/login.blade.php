<x-guest-layout>
    {{-- Session Status --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-7">
        <h1 class="h-display text-3xl">¡Hola de nuevo! 👋</h1>
        <p class="text-ink-700/60 mt-1.5 text-[15px]">Inicia sesión para volver con tu manada.</p>
    </div>

    {{-- Facebook SDK (funcionalidad existente) --}}
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
    <script>
        window.fbAsyncInit = function() {
            FB.init({ appId: '2042704026343129', autoLogAppEvents: true, xfbml: true, version: 'v25.0' });
        };
        window.addEventListener('message', (event) => {
            if (!event.origin.endsWith('facebook.com')) return;
            try {
                const data = JSON.parse(event.data);
                if (data.type === 'WA_EMBEDDED_SIGNUP') { console.log('message event: ', data); }
            } catch { console.log('message event: ', event.data); }
        });
        const fbLoginCallback = (response) => {
            if (response.authResponse) { console.log('response: ', response.authResponse.code); }
            else { console.log('response: ', response); }
        }
        const launchWhatsAppSignup = () => {
            FB.login(fbLoginCallback, {
                config_id: '26048236518194952',
                response_type: 'code',
                override_default_response_type: true,
                extras: { setup: {} },
            });
        }
    </script>

    {{-- Botón social --}}
    <button type="button" onclick="launchWhatsAppSignup()"
            class="w-full flex items-center justify-center gap-2.5 rounded-full px-5 py-3 mb-5
                   bg-white ring-1 ring-cream-300 text-ink-700 font-semibold text-sm
                   hover:ring-brand-200 hover:bg-cream-50 active:scale-[.98] transition-all">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877f2"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07Z"/></svg>
        Continuar con Facebook
    </button>

    <div class="flex items-center gap-3 mb-5">
        <span class="h-px flex-1 bg-cream-300"></span>
        <span class="text-xs text-ink-700/40 font-medium">o con tu email</span>
        <span class="h-px flex-1 bg-cream-300"></span>
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
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   placeholder="••••••••" class="field">
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
