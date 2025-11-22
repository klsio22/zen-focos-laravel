<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\PomodoroController;

// Rota pública - página de boas-vindas
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return view('welcome');
})->name('welcome');

// Registrar rotas de autenticação exceto o fluxo de reset de senha
Auth::routes(['reset' => false]);

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [TaskController::class, 'index'])->name('home');

    // Tasks Routes
    Route::resource('tasks', TaskController::class);
    Route::get('/tasks/{task}/timer', [TaskController::class, 'showTimer'])->name('tasks.timer');

    // Pomodoro Routes
    Route::post('/tasks/{task}/start-session', [PomodoroController::class, 'startSession'])->name('pomodoro.start');
    Route::post('/sessions/{session}/complete', [PomodoroController::class, 'completeSession'])->name('pomodoro.complete');
    Route::post('/sessions/{session}/cancel', [PomodoroController::class, 'cancelSession'])->name('pomodoro.cancel');
    Route::post('/sessions/{session}/pause', [PomodoroController::class, 'pauseSession'])->name('pomodoro.pause');
    Route::post('/sessions/{session}/resume', [PomodoroController::class, 'resumeSession'])->name('pomodoro.resume');
    Route::get('/active-session', [PomodoroController::class, 'getActiveSession'])->name('pomodoro.active');
});
