@extends('layouts.app')

@section('title', 'Minhas Tasks')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">🍅 Minhas Tasks</h1>
        <a href="{{ route('tasks.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition shadow-lg">
            ➕ Nova Task
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        @if($tasks->count() > 0)
            <div class="space-y-4">
                @foreach($tasks as $task)
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-800">{{ $task->title }}</h3>
                            @if($task->description)
                                <p class="text-gray-600 mt-1">{{ $task->description }}</p>
                            @endif
                            <div class="flex items-center space-x-4 mt-3 text-sm text-gray-500">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                                    </svg>
                                    Estimado: {{ $task->estimated_pomodoros }} 🍅
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                    </svg>
                                    Completado: {{ $task->completed_pomodoros }} 🍅
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    {{ $task->status === 'completed' ? 'bg-green-100 text-green-800' :
                                       ($task->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $task->status === 'completed' ? 'Concluída' :
                                       ($task->status === 'in_progress' ? 'Em Progresso' : 'Pendente') }}
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-col space-y-2 ml-4 items-end">
                            <!-- Timer Circular -->
                            <div class="flex flex-col items-center justify-center">
                                <div class="relative w-24 h-24" id="timer-{{ $task->id }}">
                                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                        <!-- Background Circle -->
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                                        <!-- Progress Circle -->
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="#ef4444" stroke-width="4"
                                                stroke-dasharray="282.7" stroke-dashoffset="282.7"
                                                class="timer-progress-{{ $task->id }} transition-all"
                                                style="stroke-dasharray: 282.7; stroke-dashoffset: 282.7"/>
                                    </svg>
                                    <!-- Timer Text -->
                                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="timer-text-{{ $task->id }} text-2xl font-bold text-gray-800">25:00</span>
                                        <span class="text-xs text-gray-500">min</span>
                                    </div>
                                </div>
                                <button onclick="startPomodoro({{ $task->id }})"
                                        class="mt-3 bg-red-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-600 transition shadow font-medium">
                                    ▶️ Iniciar
                                </button>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-2 mt-4 w-full">
                                <a href="{{ route('tasks.edit', $task) }}"
                                   class="flex-1 bg-gray-500 text-white px-3 py-2 rounded-lg text-sm hover:bg-gray-600 transition text-center font-medium">
                                    ✏️ Editar
                                </a>
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta task?');" class="flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full bg-red-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-red-700 transition font-medium">
                                        🗑️ Remover
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-500 mt-4">Nenhuma task encontrada. Crie sua primeira task!</p>
                <a href="{{ route('tasks.create') }}" class="mt-4 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    Criar primeira task
                </a>
            </div>
        @endif
    </div>
</div>

@section('scripts')
<script>
    // Armazenar timers ativos
    const timers = {};

    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    function updateTimerDisplay(taskId, secondsRemaining) {
        const totalSeconds = 25 * 60; // 25 minutos
        const percent = (secondsRemaining / totalSeconds) * 100;
        const circumference = 282.7; // 2 * π * 45
        const offset = circumference - (circumference * percent) / 100;

        // Atualizar texto do timer
        document.querySelector(`.timer-text-${taskId}`).textContent = formatTime(secondsRemaining);

        // Atualizar círculo de progresso
        const circle = document.querySelector(`.timer-progress-${taskId}`);
        if (circle) {
            circle.style.strokeDashoffset = offset;
        }

        // Mudar cor baseado no tempo
        if (secondsRemaining <= 300) { // Últimos 5 minutos
            circle.style.stroke = '#f97316'; // Orange
        }
        if (secondsRemaining <= 60) { // Último minuto
            circle.style.stroke = '#dc2626'; // Red escuro
        }
    }

    function startPomodoro(taskId) {
        // Se já existe timer, cancelar
        if (timers[taskId]) {
            clearInterval(timers[taskId]);
            delete timers[taskId];
            // Resetar timer display
            document.querySelector(`.timer-text-${taskId}`).textContent = '25:00';
            document.querySelector(`.timer-progress-${taskId}`).style.strokeDashoffset = '282.7';
            document.querySelector(`.timer-progress-${taskId}`).style.stroke = '#ef4444';
        }

        // Fazer chamada para backend para registrar sessão
        fetch(`/tasks/${taskId}/start-session`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Sessão iniciada:', data);

            // Iniciar timer local
            let secondsRemaining = 25 * 60; // 25 minutos

            timers[taskId] = setInterval(() => {
                secondsRemaining--;
                updateTimerDisplay(taskId, secondsRemaining);

                // Se tempo acabou
                if (secondsRemaining <= 0) {
                    clearInterval(timers[taskId]);
                    delete timers[taskId];

                    // Notificação sonora (simples)
                    playNotificationSound();

                    // Completar sessão no backend
                    completePomodoro(taskId);
                }
            }, 1000);

            // Atualizar display inicial
            updateTimerDisplay(taskId, secondsRemaining);
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('❌ Erro ao iniciar sessão Pomodoro');
        });
    }

    function completePomodoro(taskId) {
        // Obter ID da sessão ativa do usuário
        fetch('/active-session', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.session && data.session.id) {
                // Completar sessão
                fetch(`/sessions/${data.session.id}/complete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(result => {
                    alert('🍅 Pomodoro completado! Parabéns!\n\n' + result.message);
                    location.reload();
                })
                .catch(err => console.error('Erro ao completar:', err));
            }
        })
        .catch(err => console.error('Erro ao buscar sessão:', err));
    }

    function playNotificationSound() {
        // Criar som simples usando Web Audio API
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        } catch (e) {
            console.log('Audio API não disponível');
        }
    }
</script>
@endsection
@endsection
