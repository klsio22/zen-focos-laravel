@extends('layouts.app')

@section('title', 'Editar Task')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('tasks.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2 transition">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
                Voltar para lista
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-8 border border-slate-200">
            <h1 class="text-4xl font-bold text-slate-900 mb-8 flex items-center gap-3">
                <div class="p-3 bg-amber-100 rounded-lg">
                    <x-heroicon-o-pencil class="w-8 h-8 text-amber-600" />
                </div>
                <span>Editar Task</span>
            </h1>

            <form action="{{ route('tasks.update', $task) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Título -->
                <div>
                    <label for="title" class="block text-slate-900 font-semibold mb-2 text-lg">
                        Título da Task
                        <span class="text-red-500 ml-1">*</span>
                    </label>
                    <input type="text"
                           id="title"
                           name="title"
                           value="{{ old('title', $task->title) }}"
                           class="w-full px-4 py-3 border-2 border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition @error('title') border-red-500 @enderror"
                           required>
                    @error('title')
                        <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                            <x-heroicon-o-exclamation-circle class="w-4 h-4" />
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Descrição -->
                <div>
                    <label for="description" class="block text-slate-900 font-semibold mb-2 text-lg">
                        Descrição
                    </label>
                    <textarea id="description"
                              name="description"
                              rows="5"
                              class="w-full px-4 py-3 border-2 border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition resize-none @error('description') border-red-500 @enderror">{{ old('description', $task->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                            <x-heroicon-o-exclamation-circle class="w-4 h-4" />
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-slate-900 font-semibold mb-2 text-lg">
                        Status
                        <span class="text-red-500 ml-1">*</span>
                    </label>
                    <select id="status"
                            name="status"
                            class="w-full px-4 py-3 border-2 border-slate-300 rounded-lg text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition @error('status') border-red-500 @enderror"
                            required>
                        <option value="pending" {{ old('status', $task->status) === 'pending' ? 'selected' : '' }}>
                            ⏳ Pendente
                        </option>
                        <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>
                            🔄 Em Progresso
                        </option>
                        <option value="completed" {{ old('status', $task->status) === 'completed' ? 'selected' : '' }}>
                            ✅ Concluída
                        </option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                            <x-heroicon-o-exclamation-circle class="w-4 h-4" />
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Pomodoros -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="estimated_pomodoros" class="block text-slate-900 font-semibold mb-2 text-lg">
                            Estimados
                            <span class="text-red-500 ml-1">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number"
                                   id="estimated_pomodoros"
                                   name="estimated_pomodoros"
                                   value="{{ old('estimated_pomodoros', $task->estimated_pomodoros) }}"
                                   min="1"
                                   max="20"
                                   class="flex-1 px-4 py-3 border-2 border-slate-300 rounded-lg text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition @error('estimated_pomodoros') border-red-500 @enderror"
                                   required>
                            <span class="text-3xl">🍅</span>
                        </div>
                        @error('estimated_pomodoros')
                            <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                <x-heroicon-o-exclamation-circle class="w-4 h-4" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-slate-900 font-semibold mb-2 text-lg">
                            Completados
                        </label>
                        <div class="flex items-center gap-2 px-4 py-3 bg-green-50 border-2 border-green-300 rounded-lg">
                            <span class="text-2xl font-bold text-green-600">{{ $task->completed_pomodoros }}</span>
                            <span class="text-3xl">✅</span>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex gap-4 pt-6">
                    <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition font-semibold shadow-md hover:shadow-lg flex items-center justify-center gap-2 text-lg">
                        <x-heroicon-o-arrow-up-tray class="w-6 h-6" />
                        <span>Salvar Alterações</span>
                    </button>
                    <a href="{{ route('tasks.index') }}"
                       class="flex-1 bg-slate-300 hover:bg-slate-400 text-slate-900 px-6 py-3 rounded-lg transition font-semibold text-center text-lg">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
