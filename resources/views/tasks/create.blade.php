@extends('layouts.app')

@section('title', 'Nova Task')

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
                <div class="p-3 bg-blue-100 rounded-lg">
                    <x-heroicon-o-plus class="w-8 h-8 text-blue-600" />
                </div>
                <span>Nova Task</span>
            </h1>

            <form action="{{ route('tasks.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Título -->
                <div>
                    <label for="title" class="block text-slate-900 font-semibold mb-2 text-lg">
                        Título da Task
                        <span class="text-red-500 ml-1">*</span>
                    </label>
                    <input type="text"
                           id="title"
                           name="title"
                           value="{{ old('title') }}"
                           class="w-full px-4 py-3 border-2 border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition @error('title') border-red-500 @enderror"
                           placeholder="Ex: Estudar Laravel, Fazer projeto..."
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
                              class="w-full px-4 py-3 border-2 border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition resize-none @error('description') border-red-500 @enderror"
                              placeholder="Descreva os detalhes e objetivos da task...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                            <x-heroicon-o-exclamation-circle class="w-4 h-4" />
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Pomodoros Estimados -->
                <div>
                    <label for="estimated_pomodoros" class="block text-slate-900 font-semibold mb-2 text-lg">
                        Pomodoros Estimados
                        <span class="text-red-500 ml-1">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="number"
                               id="estimated_pomodoros"
                               name="estimated_pomodoros"
                               value="{{ old('estimated_pomodoros', 1) }}"
                               min="1"
                               max="20"
                               class="flex-1 px-4 py-3 border-2 border-slate-300 rounded-lg text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition @error('estimated_pomodoros') border-red-500 @enderror"
                               required>
                        <span class="text-3xl">🍅</span>
                    </div>
                    <p class="text-slate-600 text-sm mt-2">
                        💡 Cada pomodoro = 25 minutos de foco intenso
                    </p>
                    @error('estimated_pomodoros')
                        <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                            <x-heroicon-o-exclamation-circle class="w-4 h-4" />
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Botões de Ação -->
                <div class="flex gap-4 pt-6">
                    <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition font-semibold shadow-md hover:shadow-lg flex items-center justify-center gap-2 text-lg">
                        <x-heroicon-o-check class="w-6 h-6" />
                        <span>Criar Task</span>
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
