@extends('layouts.app')

@section('title', 'Minhas Tasks')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <h1 class="text-4xl font-bold text-slate-900 dark:text-white">
                📋 Minhas Tarefas
            </h1>
            <a href="{{ route('tasks.create') }}" class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold transition shadow-lg">
                ➕ Nova Tarefa
            </a>
        </div>
        <p class="text-slate-600 dark:text-slate-400 mt-2">Organize suas tarefas e mantenha-se focado com Pomodoro</p>
    </div>

    @if($tasks->count() > 0)
        <!-- Grouped by Status -->
        @php
            $pending = $tasks->where('status', 'pending');
            $inProgress = $tasks->where('status', 'in_progress');
            $completed = $tasks->where('status', 'completed');
        @endphp

        <!-- Pending Tasks -->
        @if($pending->count() > 0)
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    ⏳ Pendentes
                    <span class="ml-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-1 rounded-full text-sm font-semibold">
                        {{ $pending->count() }}
                    </span>
                </h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($pending as $task)
                        @include('tasks.components.task-card', ['task' => $task])
                    @endforeach
                </div>
            </div>
        @endif

        <!-- In Progress Tasks -->
        @if($inProgress->count() > 0)
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    🔄 Em Progresso
                    <span class="ml-2 bg-amber-200 dark:bg-amber-900 text-amber-800 dark:text-amber-100 px-3 py-1 rounded-full text-sm font-semibold">
                        {{ $inProgress->count() }}
                    </span>
                </h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($inProgress as $task)
                        @include('tasks.components.task-card', ['task' => $task])
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Completed Tasks -->
        @if($completed->count() > 0)
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    ✅ Concluídas
                    <span class="ml-2 bg-green-200 dark:bg-green-900 text-green-800 dark:text-green-100 px-3 py-1 rounded-full text-sm font-semibold">
                        {{ $completed->count() }}
                    </span>
                </h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($completed as $task)
                        @include('tasks.components.task-card', ['task' => $task])
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-12 text-center">
            <div class="text-6xl mb-4">📝</div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">Nenhuma tarefa encontrada</h2>
            <p class="text-slate-600 dark:text-slate-400 mb-6">
                Comece criando sua primeira tarefa e use a técnica Pomodoro para aumentar sua produtividade!
            </p>
            <a href="{{ route('tasks.create') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition shadow-lg">
                ➕ Criar Primeira Tarefa
            </a>
        </div>
    @endif
</div>

@section('scripts')
<script>
    // Script será carregado do componente task-card
</script>
@endsection
@endsection

