@extends('layouts.app')

@section('title', 'Minhas Tasks')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <h1 class="text-4xl font-bold text-slate-900 flex items-center gap-2">
                <x-heroicon-o-clipboard-document-list class="w-10 h-10" />
                <span>Minhas Tarefas</span>
            </h1>
            <a href="{{ route('tasks.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition shadow-lg flex items-center gap-2">
                <x-heroicon-o-plus class="w-5 h-5" />
                <span>Nova Tarefa</span>
            </a>
        </div>
        <p class="text-slate-600 mt-2">Organize suas tarefas e mantenha-se focado com Pomodoro</p>
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
                <h2 class="text-2xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <x-heroicon-o-clock class="w-6 h-6" />
                    <span>Pendentes</span>
                    <span class="ml-2 bg-slate-300 text-slate-800 px-3 py-1 rounded-full text-sm font-semibold">
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
                <h2 class="text-2xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <x-heroicon-o-arrow-path class="w-6 h-6 animate-spin" />
                    <span>Em Progresso</span>
                    <span class="ml-2 bg-amber-200 text-amber-800 px-3 py-1 rounded-full text-sm font-semibold">
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
                <h2 class="text-2xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <x-heroicon-o-check-circle class="w-6 h-6" />
                    <span>Concluídas</span>
                    <span class="ml-2 bg-green-200 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
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
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <div class="flex justify-center mb-4">
                <x-heroicon-o-document class="w-24 h-24 text-slate-300" />
            </div>
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Nenhuma tarefa encontrada</h2>
            <p class="text-slate-600 mb-6">
                Comece criando sua primeira tarefa e use a técnica Pomodoro para aumentar sua produtividade!
            </p>
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition shadow-lg">
                <x-heroicon-o-plus class="w-5 h-5" />
                <span>Criar Primeira Tarefa</span>
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

