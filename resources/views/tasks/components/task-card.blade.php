<div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow border border-slate-300 overflow-hidden task-card" data-task-id="{{ $task->id }}">
    @php
        // calcular progresso e determinar se a task já atingiu 100%
        $progress = $task->estimated_pomodoros > 0 ? ($task->completed_pomodoros / $task->estimated_pomodoros) * 100 : 0;
        $isComplete = $task->estimated_pomodoros > 0 && $task->completed_pomodoros >= $task->estimated_pomodoros;
        // displayStatus respeita o status real, mas força 'completed' quando atingido 100%
        $displayStatus = $isComplete ? 'completed' : $task->status;
    @endphp
    <!-- Header com checkbox e título -->
    <div class="p-4 pb-3 border-b border-slate-300">
        <div class="flex items-start gap-3">
         <input type="checkbox"
             class="mt-1.5 w-5 h-5 rounded border-2 border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"
            data-estimated="{{ $task->estimated_pomodoros }}"
            data-completed="{{ $task->completed_pomodoros }}"
             {{ $displayStatus === 'completed' ? 'checked' : '' }}
             onchange="updateTaskStatus({{ $task->id }}, this.checked, event)"
         >
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-slate-900 {{ $displayStatus === 'completed' ? 'line-through text-slate-500' : '' }}">
                    {{ $task->title }}
                </h3>
                @if($task->description)
                    <p class="text-sm text-slate-600 mt-1 line-clamp-2">
                        {{ $task->description }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Pomodoro Stats -->
    <div class="p-4 pb-3 border-b border-slate-300">
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center">
                <p class="text-xs text-slate-600 uppercase tracking-wider">Estimado</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">
                    {{ $task->estimated_pomodoros }} 🍅
                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-slate-600 uppercase tracking-wider">Concluído</p>
                <p class="text-2xl font-bold {{ $task->completed_pomodoros > 0 ? 'text-green-600' : 'text-slate-400' }} mt-1">
                    {{ $task->completed_pomodoros }} 🍅
                </p>
            </div>
        </div>
        <!-- Small active timer preview (hidden unless this task has an active session) -->
        <div class="mt-3 flex items-center justify-center">
            <div id="task-timer-{{ $task->id }}" class="task-small-timer hidden items-center gap-2 text-slate-700">
                <svg width="40" height="40" viewBox="0 0 100 100" class="mr-2">
                    <circle cx="50" cy="50" r="44" stroke="#e2e8f0" stroke-width="8" fill="none" />
                    <circle cx="50" cy="50" r="44" stroke="#3b82f6" stroke-width="8" fill="none" stroke-linecap="round"
                        stroke-dasharray="276.46" stroke-dashoffset="0" class="task-timer-ring transition-all" transform="rotate(-90 50 50)" />
                </svg>
                <div class="text-sm font-mono" data-time>--:--</div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mt-3 w-full bg-slate-300 rounded-full h-2 overflow-hidden">
            <div class="bg-green-500 h-full transition-all rounded-full" style="width: {{ $progress }}%"></div>
        </div>
        <p class="text-xs text-slate-600 mt-1 text-right">
            {{ number_format($progress, 0) }}% concluído
        </p>
    </div>

    <!-- Status Badge -->
    <div class="p-4 pb-3 border-b border-slate-300">
        <div class="flex justify-center">
                <span class="px-4 py-1.5 rounded-full text-sm font-semibold flex items-center gap-2
                {{ $displayStatus === 'completed' ? 'bg-green-100 text-green-800' :
                   ($displayStatus === 'in_progress' ? 'bg-amber-100 text-amber-800' :
                    'bg-slate-200 text-slate-800') }}">
                @if($displayStatus === 'completed')
                    <x-heroicon-o-check-circle class="w-5 h-5" />
                    <span>Concluída</span>
                @elseif($displayStatus === 'in_progress')
                    <x-heroicon-o-arrow-path class="w-5 h-5 animate-spin" />
                    <span>Em Progresso</span>
                @else
                    <x-heroicon-o-clock class="w-5 h-5" />
                    <span>Pendente</span>
                @endif
            </span>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="p-4 flex flex-col gap-2">
        @if($displayStatus !== 'completed')
            <a href="{{ route('tasks.timer', $task) }}"
               class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2"
            >
                <x-heroicon-o-play class="w-5 h-5" />
                <span>Ver Pomodoro</span>
            </a>
        @endif

        <div class="grid grid-cols-2 gap-2">
            <a href="{{ route('tasks.edit', $task) }}"
               class="bg-slate-500 hover:bg-slate-600 text-white font-semibold py-2 rounded-lg transition text-center flex items-center justify-center gap-2"
            >
                <x-heroicon-o-pencil class="w-5 h-5" />
                <span>Editar</span>
            </a>
            <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta tarefa?');" class="w-full">
                @csrf
                @method('DELETE')
                <button type="submit" class="flex items-center justify-center gap-2 w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded-lg transition">
                    <x-heroicon-o-trash class="w-5 h-5" />
                    <span>Remover</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Initialize task-card timers once per page
    if (!window.__taskCardTimerInit) {
        window.__taskCardTimerInit = true;
        window.__taskCardTimers = new Map(); // taskId -> intervalId

        async function fetchActiveSession() {
            try {
                const res = await fetch('/active-session', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return null;
                const data = await res.json();
                return data.session || null;
            } catch (e) {
                console.error('Erro ao buscar sessão ativa:', e);
                return null;
            }
        }

        function updateCardTimerDisplay(cardEl, secondsRemaining, duration) {
            const timerEl = cardEl.querySelector('.task-small-timer');
            if (!timerEl) return;
            const timeEl = timerEl.querySelector('[data-time]');
            const ring = cardEl.querySelector('.task-timer-ring');

            // format mm:ss
            const mins = Math.floor(secondsRemaining / 60);
            const secs = Math.max(secondsRemaining % 60, 0);
            if (timeEl) timeEl.textContent = `${String(mins).padStart(2,'0')}:${String(secs).padStart(2,'0')}`;

            // update ring (circumference for r=44 is ~276.46)
            if (ring && typeof duration === 'number' && duration > 0) {
                const total = duration * 60;
                const percent = Math.max(0, Math.min(1, secondsRemaining / total));
                const circumference = 276.46;
                const offset = circumference - (circumference * percent);
                ring.style.strokeDashoffset = String(offset);
            }
        }

        function showCardTimer(cardEl) {
            const timerEl = cardEl.querySelector('.task-small-timer');
            if (timerEl) timerEl.classList.remove('hidden');
        }

        function hideCardTimer(cardEl) {
            const timerEl = cardEl.querySelector('.task-small-timer');
            if (timerEl) timerEl.classList.add('hidden');
        }

        // Start polling and syncing timers
        (function initPolling() {
            let activeSessionCached = null;

            async function sync() {
                const session = await fetchActiveSession();

                // find all cards
                const cards = document.querySelectorAll('.task-card');

                // If no active session, clear any running card timers
                if (!session) {
                    cards.forEach(card => {
                        const tid = Number(card.dataset.taskId || 0);
                        if (window.__taskCardTimers.has(tid)) {
                            clearInterval(window.__taskCardTimers.get(tid));
                            window.__taskCardTimers.delete(tid);
                        }
                        hideCardTimer(card);
                    });
                    activeSessionCached = null;
                    return;
                }

                // If we have a session, compute elapsed and remaining
                const sessionTaskId = Number(session.task_id);
                const duration = session.duration || 25;
                const startTime = new Date(session.start_time).getTime();

                cards.forEach(card => {
                    const tid = Number(card.dataset.taskId || 0);

                    if (tid === sessionTaskId) {
                        // Show timer
                        showCardTimer(card);

                        // compute remaining seconds
                        const elapsed = Math.floor((Date.now() - startTime) / 1000);
                        let secondsRemaining = Math.max(duration * 60 - elapsed, 0);

                        // If there's already an interval for this card, clear it first
                        if (window.__taskCardTimers.has(tid)) {
                            clearInterval(window.__taskCardTimers.get(tid));
                            window.__taskCardTimers.delete(tid);
                        }

                        // update immediately
                        updateCardTimerDisplay(card, secondsRemaining, duration);

                        // start ticking every second
                        const intervalId = setInterval(() => {
                            secondsRemaining = Math.max(secondsRemaining - 1, 0);
                            updateCardTimerDisplay(card, secondsRemaining, duration);
                            if (secondsRemaining <= 0) {
                                clearInterval(intervalId);
                                window.__taskCardTimers.delete(tid);
                                // hide when ended
                                hideCardTimer(card);
                            }
                        }, 1000);

                        window.__taskCardTimers.set(tid, intervalId);
                    } else {
                        // Not the active task: ensure hidden and clear any interval
                        if (window.__taskCardTimers.has(tid)) {
                            clearInterval(window.__taskCardTimers.get(tid));
                            window.__taskCardTimers.delete(tid);
                        }
                        hideCardTimer(card);
                    }
                });

                activeSessionCached = session;
            }

            // initial sync and then poll every 5 seconds
            sync();
            setInterval(sync, 5000);
        })();
    }
    function updateTaskStatus(taskId, isChecked, evt) {
        // evt may be passed from the onchange handler
        const target = evt ? evt.target : null;
        const cardElement = target ? target.closest('.bg-white') : null;
        const titleElement = cardElement ? cardElement.querySelector('h3') : null;
        const title = titleElement ? titleElement.textContent.trim() : '';

        // read data attributes to compute new completed_pomodoros
        const estimated = target ? parseInt(target.dataset.estimated || '0', 10) : 0;
        const completed = target ? parseInt(target.dataset.completed || '0', 10) : 0;

        // If user checks the box, consider it fully completed (set completed = estimated)
        // If user unchecks, decrement completed by 1 (but not below 0) so it returns to previous progress
        let newCompleted = completed;
        if (isChecked) {
            newCompleted = estimated;
        } else {
            newCompleted = Math.max(completed - 1, 0);
        }

        const newStatus = newCompleted >= estimated && estimated > 0 ? 'completed' : 'pending';

        fetch(`/tasks/${taskId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: newStatus,
                title: title,
                description: '',
                estimated_pomodoros: estimated || 1,
                completed_pomodoros: newCompleted
            })
        })
        .then(response => {
            if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);
            return response.json();
        })
        .then(data => {
            // reflect change in UI quickly: update checkbox/data attribute and reload to refresh counts
            if (target) {
                target.dataset.completed = String(newCompleted);
            }
            setTimeout(() => location.reload(), 200);
        })
        .catch(error => {
            console.error('Erro ao atualizar status:', error);
            alert('❌ Erro ao atualizar status: ' + error.message);
        });
    }
</script>
