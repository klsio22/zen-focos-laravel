@extends('layouts.app')

@section('title', 'Temporizador - ' . $task->title)

@section('content')
<div class="min-h-screen bg-slate-200 flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-2xl">
        <!-- Working on: Label -->
        <div class="text-center mb-12">
            <p class="text-slate-600 text-lg mb-2 flex items-center justify-center gap-2">
                <x-heroicon-o-sparkles class="w-5 h-5" />
                <span>Trabalhando em:</span>
            </p>
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900 mb-8">
                {{ $task->title }}
            </h1>

            <!-- Circular Timer -->
            <div class="flex justify-center mb-12">
                <div class="relative w-64 h-64 md:w-80 md:h-80">
                    <!-- SVG Circle Background -->
                    <svg class="w-full h-full" viewBox="0 0 200 200">
                        <!-- Background circle -->
                        <circle
                            cx="100"
                            cy="100"
                            r="90"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="text-slate-300"
                        />
                        <!-- Progress circle -->
                        <circle
                            id="timer-progress"
                            cx="100"
                            cy="100"
                            r="90"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="8"
                            stroke-dasharray="565.48"
                            stroke-dashoffset="0"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            transform="rotate(-90 100 100)"
                            class="timer-circle text-blue-600 transition-all duration-1000"
                            style="stroke-dashoffset: 0;"
                        />
                    </svg>

                    <!-- Timer Text (centered in circle) -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <div id="timer-display" class="text-6xl md:text-7xl font-bold text-slate-900 font-mono">
                                25:00
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Counter -->
            <div class="mb-8">
                <p class="text-slate-600 text-base mb-3">
                    Sessão {{ $task->completed_pomodoros + 1 }} de {{ $task->estimated_pomodoros }}
                </p>
                <!-- Dots indicator -->
                <div class="flex justify-center gap-2">
                    @for($i = 1; $i <= $task->estimated_pomodoros; $i++)
                        <div class="w-3 h-3 rounded-full {{ $i <= $task->completed_pomodoros ? 'bg-green-600' : ($i == $task->completed_pomodoros + 1 ? 'bg-blue-600' : 'bg-slate-300') }}"></div>
                    @endfor
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center gap-4 mb-8">
                <button
                    id="pause-btn"
                    onclick="toggleTimer({{ $task->id }})"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-full font-semibold transition-colors shadow-lg flex items-center gap-2"
                >
                    <x-heroicon-o-play id="btn-icon-play" class="w-5 h-5" />
                    <x-heroicon-o-pause id="btn-icon-pause" class="w-5 h-5 hidden" />
                    <span id="btn-text">Iniciar</span>
                </button>

                @php
                    $isLastSession = ($task->completed_pomodoros + 1) >= $task->estimated_pomodoros;
                @endphp
                @if($task->estimated_pomodoros > 1 && !$isLastSession)
                    <button
                        onclick="skipPomodoro({{ $task->id }})"
                        class="bg-amber-600 hover:bg-amber-700 text-white px-8 py-3 rounded-full font-semibold transition-colors shadow-lg flex items-center gap-2"
                    >
                        <x-heroicon-o-forward class="w-5 h-5" />
                        <span>Pular</span>
                    </button>
                @endif
            </div>

            <!-- Back Button -->
            <a href="{{ route('tasks.index') }}"
               class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 transition-colors font-medium"
            >
                <x-heroicon-o-arrow-left class="w-5 h-5" />
                <span>Voltar para Tarefas</span>
            </a>
        </div>
    </div>
</div>

<!-- Skip Confirmation Modal -->
<div id="skip-modal" class="fixed inset-0 z-50 hidden bg-black/50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Pular Pomodoro</h3>
                <p class="text-slate-600 mb-4">Tem certeza que deseja pular este pomodoro?</p>
                <div class="flex justify-end gap-3">
                    <button id="skip-cancel-btn" class="px-4 py-2 rounded-md bg-slate-100 text-slate-700 hover:bg-slate-200">Cancelar</button>
                    <button id="skip-confirm-btn" class="px-4 py-2 rounded-md bg-rose-600 text-white hover:bg-rose-700">Sim, pular</button>
                </div>
            </div>
        </div>
    </div>
</div>

    <script>
        // Timer config is passed to the module; note: the heavy logic lives in resources/js/timer.js
        window.__timerConfig = {
            taskId: {{ $task->id }},
            estimatedPomodoros: {{ $task->estimated_pomodoros }},
            completedPomodoros: {{ $task->completed_pomodoros }}
        };
    </script>

    @vite(['resources/js/timer-store.js', 'resources/js/timer.js'])

@endsection
