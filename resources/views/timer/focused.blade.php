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

                @php
                    $isLastSession = ($task->completed_pomodoros + 1) >= $task->estimated_pomodoros;
                @endphp
                @if($task->estimated_pomodoros > 1 && !$isLastSession)
                    <button
                        onclick="skipPomodoro({{ $task->id }})"
                        class="bg-amber-600 hover:bg-amber-700 text-white px-8 py-3 rounded-full font-semibold transition-colors shadow-lg flex items-center gap-2"
                    >
                        <x-heroicon-o-forward class="w-5 h-5" />
                        <span>Pular</span>
                    </button>
                @endif
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

<!-- Skip Confirmation Modal -->
<div id="skip-modal" class="fixed inset-0 z-50 hidden bg-black/50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Pular Pomodoro</h3>
                <p class="text-slate-600 mb-4">Tem certeza que deseja pular este pomodoro?</p>
                <div class="flex justify-end gap-3">
                    <button id="skip-cancel-btn" class="px-4 py-2 rounded-md bg-slate-100 text-slate-700 hover:bg-slate-200">Cancelar</button>
                    <button id="skip-confirm-btn" class="px-4 py-2 rounded-md bg-rose-600 text-white hover:bg-rose-700">Sim, pular</button>
                </div>
            </div>
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
    let pendingSkipTaskId = null; // store task id when showing skip confirmation

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

    // Fetch active / paused sessions on load and resume timer accordingly
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

            // Prefer active session if present and not paused
            if (data.active && !data.active.is_paused) {
                activeSession = data.active;

                if (activeSession.task_id === taskId) {
                    const elapsed = Math.floor((Date.now() - new Date(activeSession.start_time).getTime()) / 1000);
                    secondsRemaining = Math.max((activeSession.duration || 25) * 60 - elapsed, 0);

                    if (secondsRemaining <= 0) {
                        completePomodoro(taskId);
                        return;
                    }

                    startTimer();
                }

            } else if (Array.isArray(data.paused) && data.paused.length > 0) {
                // If there's a paused session for this task, use it to show paused state
                const pausedForThis = data.paused.find(s => Number(s.task_id) === Number(taskId));
                if (pausedForThis) {
                    activeSession = pausedForThis;
                    // use remaining_seconds if provided
                    if (typeof activeSession.remaining_seconds === 'number') {
                        secondsRemaining = activeSession.remaining_seconds;
                        updateTimerDisplay(secondsRemaining);
                    }
                    // do not auto-start if paused
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
            // Pausar: parar tick local e persistir estado pausado no servidor
            clearInterval(timerInterval);
            isRunning = false;
            if (btnPlayIcon) btnPlayIcon.classList.remove('hidden');
            if (btnPauseIcon) btnPauseIcon.classList.add('hidden');
            if (btnText) btnText.textContent = 'Retomar';

            // If there is an activeSession on backend, persist the remaining seconds and mark paused
            if (activeSession && activeSession.id) {
                fetch(`/sessions/${activeSession.id}/pause`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ remaining_seconds: secondsRemaining })
                }).then(res => res.json()).then(json => {
                    // update local activeSession snapshot
                    activeSession.is_paused = true;
                    activeSession.remaining_seconds = secondsRemaining;
                }).catch(err => console.error('Erro ao pausar sessão:', err));
            }

            return;
        }

        // Se já existe sessão ativa no backend para esta task e o timer não está no valor inicial,
        // apenas retome a contagem localmente (start_time já existe no servidor)
        if (activeSession && activeSession.task_id === taskId && secondsRemaining < 25 * 60) {
            // If the session is paused on the backend, resume it via API; otherwise start locally
            if (activeSession.is_paused) {
                try {
                    const res = await fetch(`/sessions/${activeSession.id}/resume`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ remaining_seconds: secondsRemaining })
                    });

                    if (!res.ok) throw new Error('Falha ao retomar sessão');
                    const data = await res.json();
                    activeSession = data.session || activeSession;
                    startTimer();
                } catch (err) {
                    console.error('Erro ao retomar sessão:', err);
                    // fallback to local start
                    startTimer();
                }
            } else {
                startTimer();
            }

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
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const current = data.active || null;
            if (current && current.id) {
                fetch(`/sessions/${current.id}/complete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(result => {
                    alert('Pomodoro concluído! Parabéns!');
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
        // Open the modal instead of native confirm
        showSkipModal(taskId);
    }

    function showSkipModal(taskId) {
        pendingSkipTaskId = taskId;
        const modal = document.getElementById('skip-modal');
        if (modal) modal.classList.remove('hidden');
    }

    function hideSkipModal() {
        pendingSkipTaskId = null;
        const modal = document.getElementById('skip-modal');
        if (modal) modal.classList.add('hidden');
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

    // Inicializar: buscar sessão ativa, atualizar display e ligar handlers da modal
    window.addEventListener('load', async () => {
        await fetchActiveSessionAndInit();
        updateTimerDisplay(secondsRemaining);

        // Link modal buttons
        const confirmBtn = document.getElementById('skip-confirm-btn');
        const cancelBtn = document.getElementById('skip-cancel-btn');

        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                // perform skip action: clear timer and reload
                clearInterval(timerInterval);
                isRunning = false;
                hideSkipModal();
                // keep original behavior: reload to reflect changes
                location.reload();
            });
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                hideSkipModal();
            });
        }
    });
</script>
@endsection
@endsection
