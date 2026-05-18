<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#1e3a8a] via-[#3b82f6] to-[#93c5fd] relative overflow-hidden">
        <!-- decorative elements -->
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-white opacity-20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-white opacity-20 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
        
        <div class="w-full max-w-md p-10 glass rounded-3xl shadow-2xl relative z-10 border border-white/40">
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/20 mb-4 shadow-inner">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Admin Portal</h2>
                <p class="text-sm text-gray-800 mt-2 font-medium">Selamat datang, silakan login.</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Validation Errors -->
            <x-auth-validation-errors class="mb-4" :errors="$errors" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-semibold text-gray-800 mb-1">{{ __('Email Address') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                        </div>
                        <input id="email" class="block w-full pl-10 pr-3 py-3 rounded-xl border-0 shadow-sm focus:ring-2 focus:ring-blue-500 sm:text-sm bg-white/60 backdrop-blur-md font-medium text-gray-900 placeholder-gray-600 transition-all" type="email" name="email" :value="old('email')" required autofocus placeholder="admin@lpkia.ac.id" />
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-semibold text-gray-800 mb-1">{{ __('Password') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input id="password" class="block w-full pl-10 pr-3 py-3 rounded-xl border-0 shadow-sm focus:ring-2 focus:ring-blue-500 sm:text-sm bg-white/60 backdrop-blur-md font-medium text-gray-900 placeholder-gray-600 transition-all" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between mb-8">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-400 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 bg-white/60 cursor-pointer" name="remember">
                        <span class="ml-2 text-sm font-medium text-gray-800">{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-sm text-blue-900 hover:text-blue-950 font-bold transition-colors" href="{{ route('password.request') }}">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform hover:translate-y-[-2px]">
                        {{ __('Sign In to Admin') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
