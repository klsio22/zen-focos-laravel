@extends('layouts.app')

@section('title', 'Temporizador - ' . $task->title)

@section('content')
<div class="min-h-screen bg-slate-200 flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-2xl">
        <!-- Working on: Label -->
        <div class="text-center mb-12">
            <p class="text-slate-600 text-lg mb-2 flex items-center justify-center gap-2">
                <x-heroicon-o-sparkles class="w-5 h-5" />
                <span>Trabalhando em:</span>
            </p>
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900 mb-8">
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
                            class="text-slate-300"
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
                            class="timer-circle text-blue-600 transition-all duration-1000"
                            style="stroke-dashoffset: 0;"
                        />
                    </svg>

                    <!-- Timer Text (centered in circle) -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <div id="timer-display" class="text-6xl md:text-7xl font-bold text-slate-900 font-mono">
                                25:00
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Counter -->
            <div class="mb-8">
                <p class="text-slate-600 text-base mb-3">
                    Sessão {{ $task->completed_pomodoros + 1 }} de {{ $task->estimated_pomodoros }}
                </p>
                <!-- Dots indicator -->
                <div class="flex justify-center gap-2">
                    @for($i = 1; $i <= $task->estimated_pomodoros; $i++)
                        <div class="w-3 h-3 rounded-full {{ $i <= $task->completed_pomodoros ? 'bg-green-600' : ($i == $task->completed_pomodoros + 1 ? 'bg-blue-600' : 'bg-slate-300') }}"></div>
                    @endfor
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center gap-4 mb-8">
                <button
                    id="pause-btn"
                    onclick="toggleTimer({{ $task->id }})"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-full font-semibold transition-colors shadow-lg flex items-center gap-2"
                >
                    <x-heroicon-o-play id="btn-icon-play" class="w-5 h-5" />
                    <x-heroicon-o-pause id="btn-icon-pause" class="w-5 h-5 hidden" />
                    <span id="btn-text">Iniciar</span>
                </button>

                <button
                    onclick="skipPomodoro({{ $task->id }})"
                    class="bg-amber-600 hover:bg-amber-700 text-white px-8 py-3 rounded-full font-semibold transition-colors shadow-lg flex items-center gap-2"
                >
                    <x-heroicon-o-forward class="w-5 h-5" />
                    <span>Pular</span>
                </button>
            </div>

            <!-- Back Button -->
            <a href="{{ route('tasks.index') }}"
               class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 transition-colors font-medium"
            >
                <x-heroicon-o-arrow-left class="w-5 h-5" />
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
    let activeSession = null; // will hold session data from backend if any

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
            circle.classList.add('text-blue-600');
        } else if (seconds > 60) { // 1-5 minutos
            circle.classList.remove('text-blue-600', 'text-red-600');
            circle.classList.add('text-orange-500');
        } else { // < 1 minuto
            circle.classList.remove('text-blue-600', 'text-orange-500');
            circle.classList.add('text-red-600');
        }
    }

    // Fetch active session on load and resume timer accordingly
    async function fetchActiveSessionAndInit() {
        try {
            const res = await fetch('/active-session', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) return;
            const data = await res.json();
            if (data.session) {
                activeSession = data.session;

                // If the active session belongs to this task, compute remaining seconds
                if (activeSession.task_id === taskId) {
                    const elapsed = Math.floor((Date.now() - new Date(activeSession.start_time).getTime()) / 1000);
                    secondsRemaining = Math.max((activeSession.duration || 25) * 60 - elapsed, 0);

                    if (secondsRemaining <= 0) {
                        // session expired server-side; try to complete
                        completePomodoro(taskId);
                        return;
                    }

                    // start the timer automatically to reflect real-time progress
                    startTimer();
                }
            }
        } catch (err) {
            console.error('Erro ao buscar sessão ativa:', err);
        }
    }

    async function toggleTimer(taskId) {
        const btnText = document.getElementById('btn-text');
        const btnPlayIcon = document.getElementById('btn-icon-play');
        const btnPauseIcon = document.getElementById('btn-icon-pause');

        if (isRunning) {
            // Pausar (client-side pause; server still marks session as active)
            clearInterval(timerInterval);
            isRunning = false;
            if (btnPlayIcon) btnPlayIcon.classList.remove('hidden');
            if (btnPauseIcon) btnPauseIcon.classList.add('hidden');
            if (btnText) btnText.textContent = 'Retomar';
            return;
        }

        // Se já existe sessão ativa no backend para esta task e o timer não está no valor inicial,
        // apenas retome a contagem localmente (start_time já existe no servidor)
        if (activeSession && activeSession.task_id === taskId && secondsRemaining < 25 * 60) {
            startTimer();
            return;
        }

        // Caso contrário, iniciar nova sessão no backend
        try {
            const res = await fetch(`/tasks/${taskId}/start-session`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) throw new Error('Resposta inválida ao iniciar sessão');

            const data = await res.json();
            activeSession = data.session;
            console.log('Sessão iniciada:', activeSession);
            // recompute secondsRemaining in case backend provides duration/start_time differences
            const elapsed = Math.floor((Date.now() - new Date(activeSession.start_time).getTime()) / 1000);
            secondsRemaining = Math.max((activeSession.duration || 25) * 60 - elapsed, 0);
            startTimer();
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao iniciar sessão');
        }
    }

    function startTimer() {
        isRunning = true;
        const btnPlayIcon = document.getElementById('btn-icon-play');
        const btnPauseIcon = document.getElementById('btn-icon-pause');
        const btnText = document.getElementById('btn-text');

        if (btnPlayIcon) btnPlayIcon.classList.add('hidden');
        if (btnPauseIcon) btnPauseIcon.classList.remove('hidden');
        if (btnText) btnText.textContent = 'Pausar';

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
                    alert('Pomodoro concluído! Parabéns!');
                    // reset local state and reload to reflect updated task counts
                    activeSession = null;
                    secondsRemaining = 25 * 60;
                    isRunning = false;
                    location.reload();
                })
                .catch(err => console.error('Erro ao completar:', err));
            }
        })
        .catch(err => console.error('Erro ao buscar sessão:', err));
    }

    function skipPomodoro(taskId) {
        if (confirm('Tem certeza que deseja pular este pomodoro?')) {
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

    // Inicializar: buscar sessão ativa e atualizar display
    window.addEventListener('load', async () => {
        await fetchActiveSessionAndInit();
        updateTimerDisplay(secondsRemaining);
    });
</script>
@endsection
@endsection
