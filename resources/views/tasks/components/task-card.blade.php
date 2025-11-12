<div class="bg-white dark:bg-slate-800 rounded-lg shadow-md hover:shadow-lg transition-shadow border border-slate-200 dark:border-slate-700 overflow-hidden">
    <!-- Header com checkbox e título -->
    <div class="p-4 pb-3 border-b border-slate-200 dark:border-slate-700">
        <div class="flex items-start gap-3">
            <input type="checkbox"
                   class="mt-1.5 w-5 h-5 rounded border-2 border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"
                   {{ $task->status === 'completed' ? 'checked' : '' }}
                   onchange="updateTaskStatus({{ $task->id }}, this.checked)"
            >
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white {{ $task->status === 'completed' ? 'line-through text-slate-500 dark:text-slate-400' : '' }}">
                    {{ $task->title }}
                </h3>
                @if($task->description)
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1 line-clamp-2">
                        {{ $task->description }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Pomodoro Stats -->
    <div class="p-4 pb-3 border-b border-slate-200 dark:border-slate-700">
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center">
                <p class="text-xs text-slate-600 dark:text-slate-400 uppercase tracking-wider">Estimado</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
                    {{ $task->estimated_pomodoros }} 🍅
                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-slate-600 dark:text-slate-400 uppercase tracking-wider">Concluído</p>
                <p class="text-2xl font-bold {{ $task->completed_pomodoros > 0 ? 'text-green-600 dark:text-green-400' : 'text-slate-400 dark:text-slate-600' }} mt-1">
                    {{ $task->completed_pomodoros }} 🍅
                </p>
            </div>
        </div>

        <!-- Progress Bar -->
        @php
            $progress = $task->estimated_pomodoros > 0 ? ($task->completed_pomodoros / $task->estimated_pomodoros) * 100 : 0;
        @endphp
        <div class="mt-3 w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
            <div class="bg-green-500 dark:bg-green-400 h-full transition-all rounded-full" style="width: {{ $progress }}%"></div>
        </div>
        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 text-right">
            {{ number_format($progress, 0) }}% concluído
        </p>
    </div>

    <!-- Status Badge -->
    <div class="p-4 pb-3 border-b border-slate-200 dark:border-slate-700">
        <div class="flex justify-center">
            <span class="px-4 py-1.5 rounded-full text-sm font-semibold
                {{ $task->status === 'completed' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100' :
                   ($task->status === 'in_progress' ? 'bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-100' :
                    'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-300') }}">
                {{ $task->status === 'completed' ? '✅ Concluída' :
                   ($task->status === 'in_progress' ? '🔄 Em Progresso' : '⏳ Pendente') }}
            </span>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="p-4 flex flex-col gap-2">
        @if($task->status !== 'completed')
            <a href="{{ route('tasks.timer', $task) }}"
               class="w-full bg-linear-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 dark:from-blue-600 dark:to-blue-700 dark:hover:from-blue-700 dark:hover:to-blue-800 text-white font-semibold py-3 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2"
            >
                <span>▶️</span>
                <span>Iniciar Pomodoro</span>
            </a>
        @endif

        <div class="grid grid-cols-2 gap-2">
            <a href="{{ route('tasks.edit', $task) }}"
               class="bg-slate-500 hover:bg-slate-600 dark:bg-slate-600 dark:hover:bg-slate-700 text-white font-semibold py-2 rounded-lg transition text-center"
            >
                ✏️ Editar
            </a>
            <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('⚠️ Tem certeza que deseja remover esta tarefa?');" class="w-full">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 text-white font-semibold py-2 rounded-lg transition">
                    🗑️ Remover
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function updateTaskStatus(taskId, isChecked) {
        const status = isChecked ? 'completed' : 'pending';

        fetch(`/tasks/${taskId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Status atualizado:', data);
            // Recarregar a página para refletir as mudanças
            setTimeout(() => location.reload(), 300);
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('❌ Erro ao atualizar status');
        });
    }
</script>
