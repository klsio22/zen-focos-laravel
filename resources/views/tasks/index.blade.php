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
                        <div class="flex flex-col space-y-2 ml-4">
                            <button onclick="startPomodoro({{ $task->id }})"
                                    class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-600 transition shadow">
                                🍅 Iniciar Pomodoro
                            </button>
                            <a href="{{ route('tasks.edit', $task) }}"
                               class="bg-gray-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-600 transition text-center">
                                ✏️ Editar
                            </a>
                            <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta task?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition">
                                    🗑️ Remover
                                </button>
                            </form>
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
function startPomodoro(taskId) {
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
        alert('🍅 ' + data.message + '\n\nVocê tem 25 minutos de foco pela frente!');
        // Aqui você pode implementar um timer visual
        location.reload();
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao iniciar sessão Pomodoro');
    });
}
</script>
@endsection
@endsection
