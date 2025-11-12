@extends('layouts.app')

@section('title', 'Editar Task')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('tasks.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar para lista
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">✏️ Editar Task</h1>

        <form action="{{ route('tasks.update', $task) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="title" class="block text-gray-700 font-medium mb-2">
                    Título da Task <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="title"
                       name="title"
                       value="{{ old('title', $task->title) }}"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       required>
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block text-gray-700 font-medium mb-2">
                    Descrição
                </label>
                <textarea id="description"
                          name="description"
                          rows="4"
                          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="status" class="block text-gray-700 font-medium mb-2">
                    Status <span class="text-red-500">*</span>
                </label>
                <select id="status"
                        name="status"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                    <option value="pending" {{ old('status', $task->status) === 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>Em Progresso</option>
                    <option value="completed" {{ old('status', $task->status) === 'completed' ? 'selected' : '' }}>Concluída</option>
                </select>
                @error('status')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="estimated_pomodoros" class="block text-gray-700 font-medium mb-2">
                    Pomodoros Estimados 🍅 <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       id="estimated_pomodoros"
                       name="estimated_pomodoros"
                       value="{{ old('estimated_pomodoros', $task->estimated_pomodoros) }}"
                       min="1"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       required>
                <p class="text-gray-500 text-sm mt-1">Completados: {{ $task->completed_pomodoros }} 🍅</p>
                @error('estimated_pomodoros')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex space-x-4">
                <button type="submit"
                        class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-medium shadow-lg">
                    💾 Salvar Alterações
                </button>
                <a href="{{ route('tasks.index') }}"
                   class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition text-center font-medium">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
