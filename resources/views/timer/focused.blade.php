@extends('layouts.app')

@section('title', 'Temporizador - ' . $task->title)

@section('content')
<div class="min-h-screen bg-linear-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-2xl">
        <!-- Working on: Label -->
        <div class="text-center mb-12">
            <p class="text-slate-600 dark:text-slate-400 text-lg mb-2">
                ✨ Trabalhando em:
            </p>
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900 dark:text-white mb-8">
                {{ $task->title }}
            </h1>

            <!-- Circular Timer -->
            <div class="flex justify-center mb-12">
                <div class="relative w-64 h-64 md:w-80 md:h-80">
                    <!-- SVG Circle Background -->
                    <svg class="w-full h-full" viewBox="0 0 200 200">
                        <!-- Background circle -->
                        <circle
                            cx="100"
                            cy="100"
                            r="90"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="text-slate-200 dark:text-slate-700"
                        />
                        <!-- Progress circle -->
                        <circle
                            id="timer-progress"
                            cx="100"
                            cy="100"
                            r="90"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="8"
                            stroke-dasharray="565.48"
                            stroke-dashoffset="0"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            transform="rotate(-90 100 100)"
                            class="timer-circle text-blue-500 dark:text-blue-400 transition-all duration-1000"
                            style="stroke-dashoffset: 0;"
                        />
                    </svg>

                    <!-- Timer Text (centered in circle) -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <div id="timer-display" class="text-6xl md:text-7xl font-bold text-slate-900 dark:text-white font-mono">
                                25:00
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Counter -->
            <div class="mb-8">
                <p class="text-slate-600 dark:text-slate-400 text-base mb-3">
                    Sessão {{ $task->completed_pomodoros + 1 }} de {{ $task->estimated_pomodoros }}
                </p>
                <!-- Dots indicator -->
                <div class="flex justify-center gap-2">
                    @for($i = 1; $i <= $task->estimated_pomodoros; $i++)
                        <div class="w-3 h-3 rounded-full {{ $i <= $task->completed_pomodoros ? 'bg-green-500 dark:bg-green-400' : ($i == $task->completed_pomodoros + 1 ? 'bg-blue-500 dark:bg-blue-400' : 'bg-slate-300 dark:bg-slate-600') }}"></div>
                    @endfor
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center gap-4 mb-8">
                <button
                    id="pause-btn"
                    onclick="toggleTimer({{ $task->id }})"
                    class="bg-slate-700 hover:bg-slate-800 dark:bg-slate-600 dark:hover:bg-slate-500 text-white px-8 py-3 rounded-full font-semibold transition-colors shadow-lg flex items-center gap-2"
                >
                    <span id="btn-icon">⏸</span>
                    <span id="btn-text">Pausar</span>
                </button>

                <button
                    onclick="skipPomodoro({{ $task->id }})"
                    class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white px-8 py-3 rounded-full font-semibold transition-colors shadow-lg flex items-center gap-2"
                >
                    <span>⏭</span>
                    <span>Pular</span>
                </button>
            </div>

            <!-- Back Button -->
            <a href="{{ route('tasks.index') }}"
               class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors font-medium"
            >
                <span>←</span>
                <span>Voltar para Tarefas</span>
            </a>
        </div>
    </div>
</div>

@section('scripts')
<script>
    const taskId = {{ $task->id }};
    const estimatedPomodoros = {{ $task->estimated_pomodoros }};
    const completedPomodoros = {{ $task->completed_pomodoros }};

    let timerInterval = null;
    let isRunning = false;
    let secondsRemaining = 25 * 60;

    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    function updateTimerDisplay(seconds) {
        const totalSeconds = 25 * 60;
        const percent = (seconds / totalSeconds) * 100;

        // Atualizar texto
        document.getElementById('timer-display').textContent = formatTime(seconds);

        // Atualizar círculo
        const circumference = 565.48; // 2 * π * 90
        const offset = circumference - (circumference * percent) / 100;
        document.getElementById('timer-progress').style.strokeDashoffset = offset;

        // Mudar cor baseado no tempo
        const circle = document.getElementById('timer-progress');
        if (seconds > 300) { // > 5 minutos
            circle.classList.remove('text-orange-500', 'text-red-600');
            circle.classList.add('text-blue-500', 'dark:text-blue-400');
        } else if (seconds > 60) { // 1-5 minutos
            circle.classList.remove('text-blue-500', 'dark:text-blue-400', 'text-red-600');
            circle.classList.add('text-orange-500');
        } else { // < 1 minuto
            circle.classList.remove('text-blue-500', 'dark:text-blue-400', 'text-orange-500');
            circle.classList.add('text-red-600');
        }
    }

    function toggleTimer(taskId) {
        if (isRunning) {
            // Pausar
            clearInterval(timerInterval);
            isRunning = false;
            document.getElementById('btn-icon').textContent = '▶';
            document.getElementById('btn-text').textContent = 'Retomar';
        } else {
            // Se ainda não foi iniciado, começar sessão no backend
            if (secondsRemaining === 25 * 60) {
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
                    startTimer();
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('❌ Erro ao iniciar sessão');
                });
            } else {
                // Retomar timer já iniciado
                startTimer();
            }
        }
    }

    function startTimer() {
        isRunning = true;
        document.getElementById('btn-icon').textContent = '⏸';
        document.getElementById('btn-text').textContent = 'Pausar';

        timerInterval = setInterval(() => {
            secondsRemaining--;
            updateTimerDisplay(secondsRemaining);

            if (secondsRemaining <= 0) {
                clearInterval(timerInterval);
                isRunning = false;
                completePomodoro(taskId);
            }
        }, 1000);
    }

    function completePomodoro(taskId) {
        playNotificationSound();

        fetch('/active-session', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.session && data.session.id) {
                fetch(`/sessions/${data.session.id}/complete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(result => {
                    // Mostrar notificação e redirecionar
                    alert('✅ Pomodoro concluído! Parabéns!');
                    location.reload();
                })
                .catch(err => console.error('Erro ao completar:', err));
            }
        })
        .catch(err => console.error('Erro ao buscar sessão:', err));
    }

    function skipPomodoro(taskId) {
        if (confirm('⚠️ Tem certeza que deseja pular este pomodoro?')) {
            clearInterval(timerInterval);
            isRunning = false;
            location.reload();
        }
    }

    function playNotificationSound() {
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

    // Inicializar display
    updateTimerDisplay(secondsRemaining);
</script>
@endsection
@endsection
