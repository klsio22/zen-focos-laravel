# 🎯 Base do Projeto Laravel - ZenFocos

## 📋 **Estrutura Base Simplificada**

### **1. Configuração Inicial**

```bash
# Criar novo projeto Laravel
composer create-project laravel/laravel zenfocos
cd zenfocos

# Instalar dependências extras
composer require laravel/ui
php artisan ui bootstrap --auth
npm install && npm run dev
```

### **2. Estrutura de Pastas Base**

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── TaskController.php
│   │   ├── PomodoroController.php
│   │   └── AuthController.php
│   └── Middleware/
├── Models/
│   ├── User.php
│   ├── Task.php
│   └── PomodoroSession.php
├── Providers/
resources/
├── views/
│   ├── layouts/
│   ├── tasks/
│   └── auth/
database/
├── migrations/
├── seeders/
routes/
├── web.php
├── api.php
```

## 🗄️ **Migrações do Banco de Dados**

### **Migration: Create Tasks Table**

```php
<?php
// database/migrations/2024_01_01_create_tasks_table.php
public function up()
{
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->text('description')->nullable();
        $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
        $table->integer('estimated_pomodoros')->default(1);
        $table->integer('completed_pomodoros')->default(0);
        $table->timestamps();
    });
}
```

### **Migration: Create Pomodoro Sessions Table**

```php
<?php
// database/migrations/2024_01_01_create_pomodoro_sessions_table.php
public function up()
{
    Schema::create('pomodoro_sessions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('task_id')->constrained()->onDelete('cascade');
        $table->integer('duration')->default(25); // minutos
        $table->timestamp('start_time');
        $table->timestamp('end_time')->nullable();
        $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
        $table->timestamps();
    });
}
```

## 🎨 **Models Base**

### **Model: Task**

```php
<?php
// app/Models/Task.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title', 
        'description',
        'status',
        'estimated_pomodoros',
        'completed_pomodoros'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pomodoroSessions()
    {
        return $this->hasMany(PomodoroSession::class);
    }
}
```

### **Model: PomodoroSession**

```php
<?php
// app/Models/PomodoroSession.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PomodoroSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
        'duration',
        'start_time',
        'end_time',
        'status'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
```

## 🚀 **Controllers Base**

### **Controller: TaskController**

```php
<?php
// app/Http/Controllers/TaskController.php
namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
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

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')->with('success', 'Task atualizada!');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();
        
        return redirect()->route('tasks.index')->with('success', 'Task removida!');
    }
}
```

### **Controller: PomodoroController**

```php
<?php
// app/Http/Controllers/PomodoroController.php
namespace App\Http\Controllers;

use App\Models\PomodoroSession;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PomodoroController extends Controller
{
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

    public function getActiveSession()
    {
        $session = PomodoroSession::where('user_id', Auth::id())
                                 ->where('status', 'active')
                                 ->first();

        return response()->json(['session' => $session]);
    }
}
```

## 🛣️ **Rotas Base**

### **routes/web.php**

```php
<?php
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
```

## 🎨 **Views Base com Blade**

### **Layout Principal**

```html
<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZenFocos - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-xl font-bold">ZenFocos</a>
            <div class="flex items-center space-x-4">
                <span>{{ Auth::user()->name }}</span>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="hover:text-blue-200">
                    Sair
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </nav>

    <main class="container mx-auto py-8 px-4">
        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
```

### **Lista de Tasks**

```html
<!-- resources/views/tasks/index.blade.php -->
@extends('layouts.app')

@section('title', 'Minhas Tasks')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Minhas Tasks</h1>
        <a href="{{ route('tasks.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Nova Task
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        @if($tasks->count() > 0)
            <div class="space-y-4">
                @foreach($tasks as $task)
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-800">{{ $task->title }}</h3>
                            <p class="text-gray-600 mt-1">{{ $task->description }}</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                <span>Estimado: {{ $task->estimated_pomodoros }} pomodoros</span>
                                <span>Completado: {{ $task->completed_pomodoros }} pomodoros</span>
                                <span class="px-2 py-1 rounded-full text-xs 
                                    {{ $task->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($task->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($task->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="startPomodoro({{ $task->id }})" 
                                    class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                                🍅 Iniciar
                            </button>
                            <a href="{{ route('tasks.edit', $task) }}" class="bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600">
                                Editar
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-8">Nenhuma task encontrada. Crie sua primeira task!</p>
        @endif
    </div>
</div>

@section('scripts')
<script>
function startPomodoro(taskId) {
    fetch(`/tasks/${taskId}/start-session`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert('Pomodoro iniciado! 25 minutos de foco.');
        // Aqui você pode implementar o timer
    });
}
</script>
@endsection
```

## 🔧 **Políticas de Acesso (Policies)**

```php
<?php
// app/Policies/TaskPolicy.php
namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task)
    {
        return $user->id === $task->user_id;
    }

    public function update(User $user, Task $task)
    {
        return $user->id === $task->user_id;
    }

    public function delete(User $user, Task $task)
    {
        return $user->id === $task->user_id;
    }
}
```

## 📦 **Arquivos de Configuração**

### **.env.example**

```env
APP_NAME=ZenFocos
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zenfocos
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

## 🚀 **Comandos para Executar**

```bash
# 1. Instalar dependências
composer install

# 2. Configurar .env
cp .env.example .env
php artisan key:generate

# 3. Executar migrações
php artisan migrate

# 4. Popular dados de teste (opcional)
php artisan db:seed

# 5. Executar servidor
php artisan serve
```

Esta base inclui todos os conceitos dos módulos solicitados:

- ✅ **Módulo 4**: Roteamento e ciclo de vida
- ✅ **Módulo 5**: Views com Blade
- ✅ **Módulo 6**: Estilização (TailwindCSS)
- ✅ **Módulo 7**: Forms e validação
- ✅ **Módulo 8**: Autenticação de usuários
