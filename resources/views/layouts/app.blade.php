<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ZenFocos - @yield('title', 'Gerenciador de Tarefas Pomodoro')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('welcome') }}" class="text-2xl font-bold hover:text-blue-100 transition flex items-center">
                        🎯 ZenFocos
                    </a>
                </div>

                @auth
                    <!-- Menu para usuários autenticados -->
                    <div class="hidden md:flex items-center space-x-6">
                        <a href="{{ route('tasks.index') }}" class="hover:text-blue-100 transition font-medium">
                            📝 Minhas Tasks
                        </a>
                        <a href="{{ route('tasks.create') }}" class="hover:text-blue-100 transition font-medium">
                            ➕ Nova Task
                        </a>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- User Info -->
                        <div class="hidden sm:block text-right">
                            <p class="font-medium">{{ Auth::user()->name }}</p>
                            <p class="text-blue-100 text-sm">{{ Auth::user()->email }}</p>
                        </div>

                        <!-- Logout Button -->
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button
                                type="submit"
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition font-medium"
                            >
                                🚪 Sair
                            </button>
                        </form>

                        <!-- Mobile Menu Toggle -->
                        <button
                            id="mobile-menu-btn"
                            class="md:hidden text-white p-2"
                            onclick="toggleMobileMenu()"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                @else
                    <!-- Menu para visitantes -->
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-white hover:text-blue-100 transition font-medium">
                            🔐 Login
                        </a>
                        <a href="{{ route('register') }}" class="bg-white text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg transition font-medium">
                            ✨ Registrar
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Mobile Menu -->
            @auth
                <div id="mobile-menu" class="hidden md:hidden mt-4 space-y-2 border-t border-blue-400 pt-4">
                    <a href="{{ route('tasks.index') }}" class="block hover:text-blue-100 transition font-medium">
                        📝 Minhas Tasks
                    </a>
                    <a href="{{ route('tasks.create') }}" class="block hover:text-blue-100 transition font-medium">
                        ➕ Nova Task
                    </a>
                </div>
            @endauth
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto py-8 px-4">
        <!-- Success Messages -->
        @if(session('success'))
            <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded" role="alert">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">✅</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Error Messages -->
        @if(session('error'))
            <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded" role="alert">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">❌</span>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16 py-8">
        <div class="container mx-auto px-4 text-center text-gray-600">
            <p>&copy; 2025 ZenFocos - Desenvolvido com ❤️ usando Laravel 12</p>
        </div>
    </footer>

    <!-- Scripts -->
    @yield('scripts')

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }
    </script>
</body>
</html>
