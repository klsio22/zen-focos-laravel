<?php

namespace App\Http\Controllers;

use App\Models\PomodoroSession;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PomodoroController extends Controller
{
    // Rotas protegidas aplicam o middleware auth; removido construtor para compatibilidade com Laravel 12

    public function startSession(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        // Encerrar sessão ativa anterior
        PomodoroSession::where('user_id', Auth::id())
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        // Criar nova sessão
        $session = PomodoroSession::create([
            'user_id' => Auth::id(),
            'task_id' => $task->id,
            'duration' => 25, // 25 minutos padrão
            'start_time' => now(),
            'status' => 'active'
        ]);

        return response()->json([
            'session' => $session,
            'message' => 'Sessão Pomodoro iniciada!'
        ]);
    }

    public function completeSession(PomodoroSession $session)
    {
        $this->authorize('update', $session);

        $session->update([
            'end_time' => now(),
            'status' => 'completed'
        ]);

        // Atualizar contador de pomodoros da task
        $session->task->increment('completed_pomodoros');

        return response()->json([
            'message' => 'Pomodoro completado!',
            'completed_pomodoros' => $session->task->completed_pomodoros
        ]);
    }

    /**
     * Cancel an active session without incrementing the task counter.
     */
    public function cancelSession(PomodoroSession $session)
    {
        $this->authorize('update', $session);

        // Only allow cancelling active sessions
        if ($session->status === 'active') {
            $session->update(['status' => 'cancelled', 'end_time' => now()]);
        }

        return response()->json(['message' => 'Sessão cancelada']);
    }

    public function getActiveSession()
    {
        $session = PomodoroSession::where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        return response()->json(['session' => $session]);
    }
}
