<?php

namespace App\Http\Controllers;

use App\Models\PomodoroSession;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PomodoroController extends Controller
{
    public function startSession(Task $task)
    {
        $this->authorize('view', $task);

        PomodoroSession::where('user_id', Auth::id())
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $session = PomodoroSession::create([
            'user_id' => Auth::id(),
            'task_id' => $task->id,
            'duration' => 25,
            'start_time' => now(),
            'status' => 'active',
            'is_paused' => false,
            'remaining_seconds' => null,
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
            'status' => 'completed',
            'is_paused' => false,
            'remaining_seconds' => null,
        ]);

        $session->task->increment('completed_pomodoros');

        return response()->json([
            'message' => 'Pomodoro completado!',
            'completed_pomodoros' => $session->task->completed_pomodoros,
        ]);
    }

    public function cancelSession(PomodoroSession $session)
    {
        $this->authorize('update', $session);

        if ($session->status === 'active' || $session->is_paused) {
            $session->update([
                'status' => 'cancelled',
                'end_time' => now(),
                'is_paused' => false,
                'remaining_seconds' => null,
            ]);
        }

        return response()->json(['message' => 'Sessão cancelada']);
    }

    public function getActiveSession()
    {
        $active = PomodoroSession::where('user_id', Auth::id())
            ->where('status', 'active')
            ->where('is_paused', false)
            ->first();

        $paused = PomodoroSession::where('user_id', Auth::id())
            ->where('is_paused', true)
            ->get();

        return response()->json(['active' => $active, 'paused' => $paused]);
    }

    public function pauseSession(Request $request, PomodoroSession $session)
    {
        $this->authorize('update', $session);

        if ($session->status !== 'active') {
            return response()->json(['message' => 'Somente sessões ativas podem ser pausadas.'], 422);
        }

        $remaining = $request->input('remaining_seconds');

        $session->update([
            'is_paused' => true,
            'remaining_seconds' => $remaining !== null ? (int) $remaining : null,
        ]);

        return response()->json(['message' => 'Sessão pausada', 'session' => $session]);
    }

    public function resumeSession(Request $request, PomodoroSession $session)
    {
        $this->authorize('update', $session);

        if (! $session->is_paused) {
            return response()->json(['message' => 'Sessão não está pausada.'], 422);
        }

        $remaining = $session->remaining_seconds ?? $request->input('remaining_seconds');

        $session->update([
            'is_paused' => false,
            'status' => 'active',
            'start_time' => now(),
            'remaining_seconds' => $remaining !== null ? (int) $remaining : null,
        ]);

        return response()->json(['message' => 'Sessão retomada', 'session' => $session]);
    }
}
