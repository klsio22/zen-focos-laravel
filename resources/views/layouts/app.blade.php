<!DOCTYPE html>
<html lang="pt-BR" id="html-root">

@includeIf('partials.header')

<body class="bg-slate-200 text-slate-900 transition-colors">
    <div class="flex h-screen">
        @auth
            <!-- Sidebar -->
            <aside
                class="w-64 bg-slate-100 border-r border-slate-300 shadow-lg flex-col hidden md:flex"
                aria-label="Navegação lateral">
                <!-- Logo -->
                <div class="p-6 border-b border-slate-300">
                    <a href="{{ route('home') }}"
                        class="text-2xl font-bold text-blue-600 flex items-center gap-2 hover:text-blue-700 transition">
                        <x-heroicon-o-rocket-launch class="w-8 h-8" />
                        <span>ZenFocos</span>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 overflow-y-auto" aria-label="Menu principal">
                    <div class="space-y-2">
                        <!-- Home -->
                        <a href="{{ route('home') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('home', 'tasks.index') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-slate-200' }} transition">
                            <x-heroicon-o-home class="w-5 h-5" />
                            <span>Home</span>
                        </a>

                        <!-- Tasks -->
                        <a href="{{ route('tasks.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('tasks.*') && !request()->routeIs('tasks.timer') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-slate-200' }} transition">
                            <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                            <span>Tarefas</span>
                        </a>

                        <!-- Additional nav items removed (Reports & Settings placeholders) -->
                    </div>
                </nav>

                <!-- User Info & Logout -->
                <div class="p-4 border-t border-slate-300 space-y-4">
                    <!-- User Info -->
                    <div class="bg-white rounded-lg p-3 border border-slate-300">
                        <p class="text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-600 truncate">{{ Auth::user()->email }}</p>
                    </div>

                    <!-- Logout -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded-lg transition flex items-center justify-center gap-2">
                            <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5" />
                            <span>Sair</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Mobile Header -->
            <div
                class="md:hidden fixed top-0 left-0 right-0 h-16 bg-slate-100 border-b border-slate-300 z-40 flex items-center justify-between px-4 shadow-md">
                <a href="{{ route('home') }}"
                    class="text-2xl font-bold text-blue-600 flex items-center gap-2">
                    <x-heroicon-o-rocket-launch class="w-6 h-6" />
                    <span>ZenFocos</span>
                </a>
                <button onclick="toggleMobileMenu()"
                    class="p-2 hover:bg-slate-200 rounded-lg transition">
                    <x-heroicon-o-bars-3 class="w-6 h-6" />
                </button>
            </div>

            <!-- Mobile Sidebar/backdrop -->
            <div id="mobile-sidebar" class="fixed inset-0 bg-black bg-opacity-50 md:hidden z-30 hidden"
                onclick="toggleMobileMenu()" onkeydown="if(event.key==='Escape') toggleMobileMenu()" aria-hidden="true">
            </div>
            <aside id="mobile-menu"
                class="fixed left-0 top-0 h-screen w-64 bg-slate-100 border-r border-slate-300 z-40 transform -translate-x-full transition-transform md:hidden"
                aria-label="Navegação móvel">
                <!-- Logo -->
                <div class="p-6 border-b border-slate-300">
                    <a href="{{ route('home') }}"
                        class="text-2xl font-bold text-blue-600 flex items-center gap-2">
                        <x-heroicon-o-rocket-launch class="w-8 h-8" />
                        <span>ZenFocos</span>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="px-4 py-6 space-y-2" aria-label="Menu móvel">
                    <a href="{{ route('home') }}" onclick="toggleMobileMenu()"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-700 hover:bg-slate-200 transition">
                        <x-heroicon-o-home class="w-5 h-5" />
                        <span>Home</span>
                    </a>
                    <a href="{{ route('tasks.index') }}" onclick="toggleMobileMenu()"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-700 hover:bg-slate-200 transition">
                        <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                        <span>Tarefas</span>
                    </a>
                </nav>

                <!-- Close Button -->
                <div class="absolute bottom-4 left-4 right-4">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded-lg transition flex items-center justify-center gap-2">
                            <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5" />
                            <span>Sair</span>
                        </button>
                    </form>
                </div>
            </aside>
        @endauth

        <!-- Main Content -->
        @include('partials.header')
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
    </script>
</body>

</html>
