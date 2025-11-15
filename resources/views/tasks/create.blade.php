@extends('layouts.app')

@section('title', 'Nova Task')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('tasks.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
            <x-heroicon-o-arrow-left class="w-5 h-5" />
            Voltar para lista
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <x-heroicon-o-plus class="w-8 h-8" />
            <span>Nova Task</span>
        </h1>

        <form action="{{ route('tasks.store') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label for="title" class="block text-slate-900 font-medium mb-2">
                    Título da Task <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="title"
                       name="title"
                       value="{{ old('title') }}"
                       class="w-full px-4 py-2 border border-slate-300 rounded-lg text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror"
                       placeholder="Ex: Estudar Laravel"
                       required>
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block text-slate-900 font-medium mb-2">
                    Descrição
                </label>
                <textarea id="description"
                          name="description"
                          rows="4"
                          class="w-full px-4 py-2 border border-slate-300 rounded-lg text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                          placeholder="Descreva os detalhes da task...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="estimated_pomodoros" class="block text-slate-900 font-medium mb-2">
                    Pomodoros Estimados <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       id="estimated_pomodoros"
                       name="estimated_pomodoros"
                       value="{{ old('estimated_pomodoros', 1) }}"
                       min="1"
                       class="w-full px-4 py-2 border border-slate-300 rounded-lg text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('estimated_pomodoros') border-red-500 @enderror"
                       required>
                <p class="text-slate-600 text-sm mt-1">Cada pomodoro = 25 minutos de foco</p>
                @error('estimated_pomodoros')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-4">
                <button type="submit"
                        class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-medium shadow-lg flex items-center justify-center gap-2">
                    <x-heroicon-o-check class="w-5 h-5" />
                    <span>Criar Task</span>
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
