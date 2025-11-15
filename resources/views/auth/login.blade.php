@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="text-5xl mb-3">🎯</div>
            <h1 class="text-3xl font-bold text-gray-800">Bem-vindo ao ZenFocos</h1>
            <p class="text-gray-600 mt-2">Faça login para acessar suas tarefas</p>
        </div>

        <!-- Card de Login -->
        <div class="bg-white rounded-xl shadow-xl p-8">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">
                        📧 Email
                    </label>
                        <input
                        id="email"
                        type="email"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition @error('email') border-red-500 @enderror"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="seu@email.com"
                        required
                        autocomplete="email"
                        autofocus
                    >
                    @error('email')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Senha -->
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-semibold mb-2">
                        🔒 Senha
                    </label>
                        <input
                        id="password"
                        type="password"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition @error('password') border-red-500 @enderror"
                        name="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    @error('password')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="mb-6 flex items-center">
                    <input
                        class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                        type="checkbox"
                        name="remember"
                        id="remember"
                        {{ old('remember') ? 'checked' : '' }}
                    >
                    <label class="ml-3 text-gray-700 font-medium" for="remember">
                        Lembrar-me
                    </label>
                </div>

                <!-- Botão Login -->
                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition shadow-lg mb-4"
                >
                    🚀 Entrar
                </button>

                <!-- Link Recuperar Senha -->
                @if (Route::has('password.request'))
                    <div class="text-center mb-4">
                        <a
                            class="text-blue-600 hover:text-blue-800 font-medium text-sm"
                            href="{{ route('password.request') }}"
                        >
                            Esqueceu a senha?
                        </a>
                    </div>
                @endif

                <!-- Divider -->
                <div class="relative mb-4">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-600">ou</span>
                    </div>
                </div>

                <!-- Link Registrar -->
                <div class="text-center">
                    <p class="text-gray-700">Não tem uma conta?</p>
                    <a
                        href="{{ route('register') }}"
                        class="text-blue-600 hover:text-blue-800 font-bold inline-block mt-2 bg-blue-50 px-4 py-2 rounded-lg hover:bg-blue-100 transition"
                    >
                        Criar nova conta
                    </a>
                </div>
            </form>

            <!-- Demo Credentials -->
            <div class="mt-6 p-4 bg-amber-50 border-l-4 border-amber-400 rounded">
                <p class="text-sm text-gray-700 font-semibold mb-2">💡 Credenciais de Teste:</p>
                <p class="text-sm text-gray-600">
                    <strong>Email:</strong> teste@zenfocos.com
                </p>
                <p class="text-sm text-gray-600">
                    <strong>Senha:</strong> senha123
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-gray-600 text-sm">
            <p>&copy; 2025 ZenFocos - Desenvolvido com ❤️</p>
        </div>
    </div>
</div>
@endsection
