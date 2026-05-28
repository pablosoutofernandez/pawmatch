<x-guest-layout>
    <div class="mb-7">
        <h1 class="h-display text-3xl">Crea tu cuenta 🐾</h1>
        <p class="text-ink-700/60 mt-1.5 text-[15px]">Únete y encuentra compañeros de paseo cerca de ti.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="field-label">Nombre</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   required autofocus autocomplete="name" placeholder="Tu nombre" class="field">
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <div>
            <label for="email" class="field-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   required autocomplete="username" placeholder="hola@ejemplo.com" class="field">
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <label for="password" class="field-label">Contraseña</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   placeholder="••••••••" class="field">
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div>
            <label for="password_confirmation" class="field-label">Confirmar contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   placeholder="••••••••" class="field">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <button type="submit" class="btn-primary w-full !py-3 mt-2">Crear cuenta</button>
    </form>

    <p class="text-center text-sm text-ink-700/60 mt-6">
        ¿Ya tienes cuenta?
        <a href="{{ route('login') }}" class="font-bold text-brand-500 hover:text-brand-600">Inicia sesión</a>
    </p>
</x-guest-layout>
