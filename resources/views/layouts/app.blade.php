<!DOCTYPE html>
<html lang="pt-BR" id="html-root">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ZenFocos - @yield('title', 'Gerenciador de Tarefas Pomodoro')</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <script>
        // Initialize theme from localStorage
        (function() {
            const theme = localStorage.getItem('zen-focos-theme') || 'light';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            localStorage.setItem('zen-focos-theme', theme);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white transition-colors">
    <div class="flex h-screen">
        @auth
            <!-- Sidebar -->
            <aside
                class="w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 shadow-lg flex-col hidden md:flex"
                aria-label="Navegação lateral">
                <!-- Logo -->
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <a href="{{ route('home') }}"
                        class="text-2xl font-bold text-blue-600 dark:text-blue-400 flex items-center gap-2 hover:text-blue-700 dark:hover:text-blue-300 transition">
                        🎯 <span>ZenFocos</span>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 overflow-y-auto" aria-label="Menu principal">
                    <div class="space-y-2">
                        <!-- Home -->
                        <a href="{{ route('home') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('home', 'tasks.index') ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }} transition">
                            <span class="text-xl">🏠</span>
                            <span>Home</span>
                        </a>

                        <!-- Tasks -->
                        <a href="{{ route('tasks.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('tasks.*') && !request()->routeIs('tasks.timer') ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }} transition">
                            <span class="text-xl">📋</span>
                            <span>Tarefas</span>
                        </a>

                        <!-- Additional nav items removed (Reports & Settings placeholders) -->
                    </div>
                </nav>

                <!-- Theme Toggle & User Info -->
                <div class="p-4 border-t border-slate-200 dark:border-slate-700 space-y-4">
                    <!-- Theme Toggle -->
                    <button onclick="toggleTheme()"
                        class="w-full flex items-center justify-between bg-slate-100 dark:bg-slate-700 px-4 py-3 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition font-medium">
                        <span>
                            <span id="theme-icon">🌙</span>
                            <span id="theme-text">Modo Escuro</span>
                        </span>
                    </button>

                    <!-- User Info -->
                    <div class="bg-slate-100 dark:bg-slate-700 rounded-lg p-3">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-600 dark:text-slate-400 truncate">{{ Auth::user()->email }}</p>
                    </div>

                    <!-- Logout -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 text-white font-semibold py-2 rounded-lg transition">
                            🚪 Sair
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Mobile Header -->
            <div
                class="md:hidden fixed top-0 left-0 right-0 h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 z-40 flex items-center justify-between px-4 shadow-md">
                <a href="{{ route('home') }}"
                    class="text-2xl font-bold text-blue-600 dark:text-blue-400 flex items-center gap-2">
                    🎯 <span>ZenFocos</span>
                </a>
                <button onclick="toggleMobileMenu()"
                    class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Sidebar/backdrop -->
            <div id="mobile-sidebar" class="fixed inset-0 bg-black bg-opacity-50 md:hidden z-30 hidden"
                onclick="toggleMobileMenu()" onkeydown="if(event.key==='Escape') toggleMobileMenu()" aria-hidden="true">
            </div>
            <aside id="mobile-menu"
                class="fixed left-0 top-0 h-screen w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 z-40 transform -translate-x-full transition-transform md:hidden"
                aria-label="Navegação móvel">
                <!-- Logo -->
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <a href="{{ route('home') }}"
                        class="text-2xl font-bold text-blue-600 dark:text-blue-400 flex items-center gap-2">
                        🎯 <span>ZenFocos</span>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="px-4 py-6 space-y-2" aria-label="Menu móvel">
                    <a href="{{ route('home') }}" onclick="toggleMobileMenu()"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                        <span class="text-xl">🏠</span>
                        <span>Home</span>
                    </a>
                    <a href="{{ route('tasks.index') }}" onclick="toggleMobileMenu()"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                        <span class="text-xl">📋</span>
                        <span>Tarefas</span>
                    </a>
                </nav>

                <!-- Theme Toggle -->
                <div class="absolute bottom-16 left-0 right-0 px-4">
                    <button onclick="toggleTheme()"
                        class="w-full flex items-center justify-between bg-slate-100 dark:bg-slate-700 px-4 py-3 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition font-medium">
                        <span>
                            <span id="theme-icon-mobile">🌙</span>
                            <span id="theme-text-mobile">Modo Escuro</span>
                        </span>
                    </button>
                </div>

                <!-- Close Button -->
                <div class="absolute bottom-4 left-4 right-4">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 text-white font-semibold py-2 rounded-lg transition">
                            🚪 Sair
                        </button>
                    </form>
                </div>
            </aside>
        @endauth

        <!-- Main Content -->
        <main class="flex-1 overflow-auto {{ auth()->check() ? 'md:pt-0 pt-16' : '' }}">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    @yield('scripts')

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const backdrop = document.getElementById('mobile-sidebar');
            menu.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }

        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');

            if (isDark) {
                html.classList.remove('dark');
                localStorage.setItem('zen-focos-theme', 'light');
                updateThemeUI('light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('zen-focos-theme', 'dark');
                updateThemeUI('dark');
            }
        }

        function updateThemeUI(theme) {
            const themeIcon = document.getElementById('theme-icon');
            const themeText = document.getElementById('theme-text');
            const themeIconMobile = document.getElementById('theme-icon-mobile');
            const themeTextMobile = document.getElementById('theme-text-mobile');

            if (theme === 'dark') {
                if (themeIcon) themeIcon.textContent = '☀️';
                if (themeText) themeText.textContent = 'Modo Claro';
                if (themeIconMobile) themeIconMobile.textContent = '☀️';
                if (themeTextMobile) themeTextMobile.textContent = 'Modo Claro';
            } else {
                if (themeIcon) themeIcon.textContent = '🌙';
                if (themeText) themeText.textContent = 'Modo Escuro';
                if (themeIconMobile) themeIconMobile.textContent = '🌙';
                if (themeTextMobile) themeTextMobile.textContent = 'Modo Escuro';
            }
        }

        // Initialize theme UI on page load
        window.addEventListener('load', () => {
            const isDark = document.documentElement.classList.contains('dark');
            updateThemeUI(isDark ? 'dark' : 'light');
        });
    </script>
</body>

</html>
