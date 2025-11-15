@extends('layouts.app')

@section('title', 'Registrar')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="text-5xl mb-3">🚀</div>
                <h1 class="text-3xl font-bold text-gray-800">Comece Agora</h1>
                <p class="text-gray-600 mt-2">Crie sua conta no ZenFocos</p>
            </div>

            <!-- Card de Registro -->
            <div class="bg-white rounded-xl shadow-xl p-8">
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Nome -->
                    <div class="mb-6">
                        <label for="name" class="block text-gray-700 font-semibold mb-2">
                            👤 Nome Completo
                        </label>
                        <input id="name" type="text"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition @error('name') border-red-500 @enderror"
                            name="name" value="{{ old('name') }}" placeholder="Seu nome" required autocomplete="name"
                            autofocus>
                        @error('name')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                        </input>
                    </div>

                    <!-- Email -->
                    <div class="mb-6">
                        <label for="email" class="block text-gray-700 font-semibold mb-2">
                            📧 Email
                        </label>
                        <input id="email" type="email"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition @error('email') border-red-500 @enderror"
                            name="email" value="{{ old('email') }}" placeholder="seu@email.com" required
                            autocomplete="email">
                        @error('email')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                        </input>
                    </div>

                    <!-- Senha -->
                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 font-semibold mb-2">
                            🔒 Senha
                        </label>
                        <input id="password" type="password"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition @error('password') border-red-500 @enderror"
                            name="password" placeholder="••••••••" required autocomplete="new-password">
                        <p class="text-gray-500 text-xs mt-1">Mínimo 8 caracteres</p>
                        @error('password')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                        </input>
                    </div>

                    <!-- Confirmar Senha -->
                    <div class="mb-6">
                        <label for="password-confirm" class="block text-gray-700 font-semibold mb-2">
                            ✅ Confirmar Senha
                        </label>
                        <input id="password-confirm" type="password"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition"
                            name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
                    </div>

                    <!-- Botão Registrar -->
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition shadow-lg mb-4">
                        ✨ Criar Conta
                    </button>

                    <!-- Divider -->
                    <div class="relative mb-4">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-600">ou</span>
                        </div>
                    </div>

                    <!-- Link Login -->
                    <div class="text-center">
                        <p class="text-gray-700">Já tem uma conta?</p>
                        <a href="{{ route('login') }}"
                            class="text-blue-600 hover:text-blue-800 font-bold inline-block mt-2 bg-blue-50 px-4 py-2 rounded-lg hover:bg-blue-100 transition">
                            Fazer login
                        </a>
                    </div>
                </form>

                <!-- Info Box -->
                <div class="mt-6 p-4 bg-green-50 border-l-4 border-green-400 rounded">
                    <p class="text-sm text-gray-700 font-semibold mb-2">✅ Por que se registrar?</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>✓ Organize suas tarefas</li>
                        <li>✓ Use a técnica Pomodoro</li>
                        <li>✓ Acompanhe seu progresso</li>
                    </ul>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-6 text-gray-600 text-sm">
                <p>&copy; 2025 ZenFocos - Desenvolvido com ❤️</p>
            </div>
        </div>
    </div>
@endsection
