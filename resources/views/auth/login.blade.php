<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Bienvenido</h2>
        <p class="text-sm text-gray-500 mt-1">Ingresa tus credenciales para continuar</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

<form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Username -->
        <div>
            <x-input-label for="username" :value="__('Usuario')" />
            <div class="relative mt-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <x-text-input id="username" class="block w-full pl-10" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" placeholder="Tu nombre de usuario" />
            </div>
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />
            <div class="relative mt-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <x-text-input id="password" class="block w-full pl-10" type="password" name="password" required autocomplete="current-password" placeholder="Tu contraseña" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between mt-5">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Recordarme') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-blue-600 hover:text-blue-800 font-medium" href="{{ route('password.request') }}">
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif
        </div>

        <div class="mt-6">
            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-150">
                {{ __('Ingresar') }}
            </button>
        </div>

        <div class="mt-5 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500 text-center leading-relaxed">
                Al ingresar y usar esta plataforma, usted declara haber leído y aceptado los
                <a href="{{ route('legal.show', 'terminos-y-condiciones') }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline font-medium">Términos y Condiciones</a>
                y la
                <a href="{{ route('legal.show', 'politica-proteccion-datos') }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline font-medium">Política de Protección de Datos</a>.
            </p>
        </div>
    </form>
</x-guest-layout>
