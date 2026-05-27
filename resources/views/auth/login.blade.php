<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script><script>
        // SDK initialization
        window.fbAsyncInit = function() {
            FB.init({
                appId: '2042704026343129', // your app ID goes here
                autoLogAppEvents: true,
                xfbml: true,
                version: 'v25.0' // Graph API version goes here
            });
        };

        // Session logging message event listener
        window.addEventListener('message', (event) => {
            console.log('message event: ', event);
            if (!event.origin.endsWith('facebook.com')) return;
            try {
                const data = JSON.parse(event.data);
                if (data.type === 'WA_EMBEDDED_SIGNUP') {
                    console.log('message event: ', data); // remove after testing
                    // your code goes here
                }
            } catch {
                console.log('message event: ', event.data); // remove after testing
                // your code goes here
            }
        });

        // Response callback
        const fbLoginCallback = (response) => {
            console.log('response: ', response);
            if (response.authResponse) {
                const code = response.authResponse.code;
                console.log('response: ', code); // remove after testing
                // your code goes here
            } else {
                console.log('response: ', response); // remove after testing
                // your code goes here
            }
        }

        // Launch method and callback registration
        const launchWhatsAppSignup = () => {
            FB.login(fbLoginCallback, {
                config_id: '26048236518194952', // your configuration ID goes here
                response_type: 'code',
                override_default_response_type: true,
                extras: {
                    setup: {},
                }
            });
        }
    </script><!-- Launch button  --><button onclick="launchWhatsAppSignup()" style="background-color: #1877f2; border: 0; border-radius: 4px; color: #fff; cursor: pointer; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: bold; height: 40px; padding: 0 24px;">Login with Facebook</button>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('register') }}">
            <x-secondary-button class="ms-3">
                    {{ __('Register') }}
            </x-secondary-button>
                </a>
            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
