<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\PomodoroController;

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('home');

    // Tasks Routes
    Route::resource('tasks', TaskController::class);

    // Pomodoro Routes
    Route::post('/tasks/{task}/start-session', [PomodoroController::class, 'startSession'])->name('pomodoro.start');
    Route::post('/sessions/{session}/complete', [PomodoroController::class, 'completeSession'])->name('pomodoro.complete');
    Route::get('/active-session', [PomodoroController::class, 'getActiveSession'])->name('pomodoro.active');
});
