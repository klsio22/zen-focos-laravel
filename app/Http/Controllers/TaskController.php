<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

class TaskController extends Controller
{
    // Rota `/home` já está protegida por middleware('auth') via routes/web.php
    // Removemos o construtor que chamava $this->middleware() para compatibilidade com Laravel 12.

    public function index()
    {
        $tasks = Task::where('user_id', Auth::id())->get();
        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        return view('tasks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_pomodoros' => 'required|integer|min:1'
        ]);

        Task::create(array_merge($validated, ['user_id' => Auth::id()]));

        return redirect()->route('tasks.index')->with('success', 'Task criada com sucesso!');
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        return view('tasks.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'estimated_pomodoros' => 'required|integer|min:1',
            'completed_pomodoros' => 'nullable|integer|min:0'
        ]);

        // Update core fields
        $task->update(
            array_filter(
                Arr::only($validated, ['title', 'description', 'status', 'estimated_pomodoros']),
                fn($v) => !is_null($v)
            )
        );

        // If completed_pomodoros was provided, update it explicitly
        if (array_key_exists('completed_pomodoros', $validated) && !is_null($validated['completed_pomodoros'])) {
            $task->completed_pomodoros = $validated['completed_pomodoros'];
            $task->save();
        }

        // Se for uma requisição AJAX/JSON, retornar JSON
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Task atualizada com sucesso!',
                'task' => $task
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task atualizada!');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task removida!');
    }

    public function showTimer(Task $task)
    {
        $this->authorize('view', $task);
        return view('timer.focused', compact('task'));
    }
}
