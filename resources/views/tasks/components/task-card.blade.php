<div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow border border-slate-300 overflow-hidden task-card"
    data-task-id="{{ $task->id }}" data-estimated="{{ $task->estimated_pomodoros }}"
    data-completed="{{ $task->completed_pomodoros }}">
    @php
        // calcular progresso e determinar se a task já atingiu 100%
        $progress =
            $task->estimated_pomodoros > 0 ? ($task->completed_pomodoros / $task->estimated_pomodoros) * 100 : 0;
        $isComplete = $task->estimated_pomodoros > 0 && $task->completed_pomodoros >= $task->estimated_pomodoros;
        // displayStatus respeita o status real, mas força 'completed' quando atingido 100%
        $displayStatus = $isComplete ? 'completed' : $task->status;
    @endphp
    <!-- Header com checkbox e título -->
    <div class="p-4 pb-3 border-b border-slate-300">
        <div class="flex items-start gap-3">
            <input type="checkbox"
                class="mt-1.5 w-5 h-5 rounded border-2 border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"
                data-estimated="{{ $task->estimated_pomodoros }}" data-completed="{{ $task->completed_pomodoros }}"
                {{ $displayStatus === 'completed' ? 'checked' : '' }}
                onchange="updateTaskStatus({{ $task->id }}, this.checked, event)">
            <div class="flex-1">
                <h3
                    class="text-lg font-semibold text-slate-900 {{ $displayStatus === 'completed' ? 'line-through text-slate-500' : '' }}">
                    {{ $task->title }}
                </h3>
                @if ($task->description)
                    <p class="text-sm text-slate-600 mt-1 line-clamp-2 max-h-full h-14">
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
                <p
                    class="text-2xl font-bold {{ $task->completed_pomodoros > 0 ? 'text-green-600' : 'text-slate-400' }} mt-1">
                    {{ $task->completed_pomodoros }} 🍅
                </p>
            </div>
        </div>
        <!-- Small active timer preview (hidden unless this task has an active session) -->
        <div class="mt-3 flex items-center justify-center">
            <div id="task-timer-{{ $task->id }}"
                class="{{ $displayStatus === 'pending' ? 'task-small-timer items-center gap-2 text-slate-700' : 'task-small-timer hidden items-center gap-2 text-slate-700' }}">
                <svg width="40" height="40" viewBox="0 0 100 100" class="mr-2">
                    <circle cx="50" cy="50" r="44" stroke="#e2e8f0" stroke-width="8" fill="none" />
                    <circle cx="50" cy="50" r="44" stroke="#3b82f6" stroke-width="8" fill="none"
                        stroke-linecap="round" stroke-dasharray="276.46" stroke-dashoffset="0"
                        class="task-timer-ring transition-all" transform="rotate(-90 50 50)" />
                </svg>
                <div class="text-sm font-mono" data-time>{{ $displayStatus === 'pending' ? '25:00' : '--:--' }}</div>
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
            <span
                class="px-4 py-1.5 rounded-full text-sm font-semibold flex items-center gap-2
                {{ $displayStatus === 'completed'
                    ? 'bg-green-100 text-green-800'
                    : ($displayStatus === 'in_progress'
                        ? 'bg-amber-100 text-amber-800'
                        : 'bg-slate-200 text-slate-800') }}">
                @if ($displayStatus === 'completed')
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
        @if ($displayStatus !== 'completed')
            <a href="{{ route('tasks.timer', $task) }}"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                <x-heroicon-o-play class="w-5 h-5" />
                <span>Ver Pomodoro</span>
            </a>
        @endif

        <div class="grid grid-cols-2 gap-2">
            <a href="{{ route('tasks.edit', $task) }}"
                class="bg-slate-500 hover:bg-slate-600 text-white font-semibold py-2 rounded-lg transition text-center flex items-center justify-center gap-2">
                <x-heroicon-o-pencil class="w-5 h-5" />
                <span>Editar</span>
            </a>
            <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                onsubmit="return confirm('Tem certeza que deseja remover esta tarefa?');" class="w-full">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="flex items-center justify-center gap-2 w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded-lg transition">
                    <x-heroicon-o-trash class="w-5 h-5" />
                    <span>Remover</span>
                </button>
            </form>
        </div>
    </div>
</div>
