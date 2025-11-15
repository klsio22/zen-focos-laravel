<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZenFocos - Gerencie suas Tarefas com Técnica Pomodoro</title>
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <span class="text-2xl font-bold text-blue-600">🎯 ZenFocos</span>
            </div>
            <div class="flex space-x-4 items-center">
                <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 font-medium">Login</a>
                <a href="{{ route('register') }}"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    Começar Agora
                </a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-16">
        <div class="text-center mb-16">
            <h1 class="text-5xl font-bold text-gray-800 mb-4">
                Produtividade com a Técnica Pomodoro 🍅
            </h1>
            <p class="text-xl text-gray-600 mb-8">
                Organize suas tarefas, mantenha o foco e acompanhe seu progresso
            </p>
            <div class="flex justify-center space-x-4">
                <a href="{{ route('register') }}"
                    class="bg-blue-600 text-white px-8 py-4 rounded-lg hover:bg-blue-700 transition text-lg font-medium shadow-lg">
                    Criar Conta Grátis
                </a>
                <a href="{{ route('login') }}"
                    class="bg-white text-blue-600 px-8 py-4 rounded-lg hover:bg-gray-50 transition text-lg font-medium shadow-lg border-2 border-blue-600">
                    Já tenho conta
                </a>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-8 mb-16">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-5xl mb-4">📝</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Gerencie Tarefas</h3>
                <p class="text-gray-600">
                    Crie e organize suas tarefas de forma simples e intuitiva
                </p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-5xl mb-4">🍅</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Técnica Pomodoro</h3>
                <p class="text-gray-600">
                    Sessões de 25 minutos de foco intenso para máxima produtividade
                </p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-5xl mb-4">📊</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Acompanhe Progresso</h3>
                <p class="text-gray-600">
                    Visualize quantos pomodoros você completou em cada tarefa
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-xl p-8 mb-16">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Como Funciona?</h2>
            <div class="grid md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-blue-600">1</span>
                    </div>
                    <h4 class="font-bold text-gray-800 mb-2">Crie uma Task</h4>
                    <p class="text-sm text-gray-600">Defina o que precisa fazer</p>
                </div>

                <div class="text-center">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-blue-600">2</span>
                    </div>
                    <h4 class="font-bold text-gray-800 mb-2">Estime Pomodoros</h4>
                    <p class="text-sm text-gray-600">Quantos blocos de 25min?</p>
                </div>

                <div class="text-center">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-blue-600">3</span>
                    </div>
                    <h4 class="font-bold text-gray-800 mb-2">Foque 25 Minutos</h4>
                    <p class="text-sm text-gray-600">Trabalhe sem distrações</p>
                </div>

                <div class="text-center">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-blue-600">4</span>
                    </div>
                    <h4 class="font-bold text-gray-800 mb-2">Faça uma Pausa</h4>
                    <p class="text-sm text-gray-600">Descanse e repita!</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-xl p-12 text-center text-white">
            <h2 class="text-3xl font-bold mb-4">Pronto para ser mais produtivo?</h2>
            <p class="text-xl mb-8">Comece agora e transforme sua forma de trabalhar!</p>
            <a href="{{ route('register') }}"
                class="bg-white text-blue-600 px-8 py-4 rounded-lg hover:bg-gray-100 transition text-lg font-medium inline-block shadow-lg">
                Criar Minha Conta Gratuita
            </a>
        </div>

        <div class="text-center mt-12 text-gray-600">
            <p class="mb-2">💡 Dica: Já tem uma conta de teste?</p>
            <p class="text-sm">
                Email: <strong>teste@zenfocos.com</strong> | Senha: <strong>senha123</strong>
            </p>
        </div>
    </main>

    <footer class="bg-white shadow-lg mt-16 py-8">
        <div class="container mx-auto px-4 text-center text-gray-600">
            <p>&copy; 2025 ZenFocos - Desenvolvido com ❤️ usando Laravel</p>
        </div>
    </footer>
</body>

</html>
