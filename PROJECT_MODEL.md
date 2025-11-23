# 📘 PROJECT MODEL - ZenFocos (Pomodoro Task Manager)

> **Documentação Técnica Completa do Sistema**  
> Versão: 1.0 | Laravel 12 + Blade Templates + JavaScript Vanilla

---

## 📋 **Índice**

1. [Visão Geral do Sistema](#visão-geral-do-sistema)
2. [Arquitetura e Estrutura de Pastas](#arquitetura-e-estrutura-de-pastas)
3. [Modelos de Dados e Relacionamentos](#modelos-de-dados-e-relacionamentos)
4. [Regras de Negócio](#regras-de-negócio)
5. [Controllers e Endpoints](#controllers-e-endpoints)
6. [Autorizações e Políticas](#autorizações-e-políticas)
7. [Frontend e Integração](#frontend-e-integração)
8. [Configuração e Dependências](#configuração-e-dependências)

---

## 🎯 **Visão Geral do Sistema**

### **Propósito**
Sistema web para gerenciamento de tarefas usando a técnica Pomodoro, permitindo:
- Criar e organizar tarefas
- Executar sessões Pomodoro (25 minutos de foco)
- Pausar/retomar sessões
- Acompanhar progresso (pomodoros completados vs estimados)
- Visualização em tempo real do timer

### **Stack Tecnológica**
- **Backend:** Laravel 12
- **Frontend:** Blade Templates + JavaScript Vanilla
- **CSS:** Tailwind CSS v4 + Blade UI Kit (Heroicons)
- **Build Tool:** Vite
- **Real-time:** Polling (consulta `/active-session` a cada 5 segundos)

---

## 🏗️ **Arquitetura e Estrutura de Pastas**

### **Backend (Laravel)**

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── TaskController.php          # CRUD de tarefas
│   │   ├── PomodoroController.php      # Gerenciamento de sessões Pomodoro
│   │   └── Auth/                       # Controllers de autenticação (Laravel UI)
│   └── Middleware/
├── Models/
│   ├── User.php                        # Modelo de usuário (Laravel padrão)
│   ├── Task.php                        # Modelo de tarefa
│   └── PomodoroSession.php             # Modelo de sessão Pomodoro
├── Policies/
│   ├── TaskPolicy.php                  # Autorização de acesso a tarefas
│   └── PomodoroSessionPolicy.php       # Autorização de sessões
└── Providers/
    └── AppServiceProvider.php          # Registro de policies

database/
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 2024_01_01_000001_create_tasks_table.php
│   ├── 2024_01_01_000002_create_pomodoro_sessions_table.php
│   └── 2025_11_15_000000_add_pause_fields_to_pomodoro_sessions.php
└── seeders/
    └── DatabaseSeeder.php

routes/
├── web.php                             # Rotas autenticadas + públicas
└── console.php
```

### **Frontend (Blade + JavaScript)**

```
resources/
├── js/
│   ├── app.js                          # Entry point JavaScript (Vite)
│   ├── bootstrap.js                    # Configurações iniciais
│   ├── timer-store.js                  # Store global do timer (polling)
│   ├── timer.js                        # Lógica do timer focado
│   └── task-cards.js                   # Lógica dos cards (sincronização)
├── views/ (Blade Templates)
│   ├── welcome.blade.php               # Página inicial pública
│   ├── home.blade.php                  # Dashboard autenticado
│   ├── layouts/
│   │   └── app.blade.php               # Layout base com sidebar
│   ├── partials/
│   │   └── header.blade.php            # Header reutilizável
│   ├── auth/
│   │   ├── login.blade.php             # Login (Laravel UI)
│   │   └── register.blade.php          # Registro (Laravel UI)
│   ├── tasks/
│   │   ├── index.blade.php             # Lista de tarefas agrupadas
│   │   ├── create.blade.php            # Form criar tarefa
│   │   ├── edit.blade.php              # Form editar tarefa
│   │   └── components/
│   │       └── task-card.blade.php     # Card reutilizável de tarefa
│   └── timer/
│       └── focused.blade.php           # Timer Pomodoro circular
└── css/
    └── app.css                         # Tailwind v4
```

---

## 🗄️ **Modelos de Dados e Relacionamentos**

### **1. User (Usuário)**

```php
// Model: app/Models/User.php (Laravel padrão + Laravel UI)
// Migration: 0001_01_01_000000_create_users_table.php

Schema:
- id: bigint (PK)
- name: string
- email: string (unique)
- password: string (hash)
- remember_token: string
- timestamps

Relacionamentos:
- hasMany(Task::class)            // Um usuário tem muitas tarefas
- hasMany(PomodoroSession::class) // Um usuário tem muitas sessões
```

### **2. Task (Tarefa)**

```php
// Model: app/Models/Task.php
// Migration: 2024_01_01_000001_create_tasks_table.php

Schema:
- id: bigint (PK)
- user_id: bigint (FK → users.id, cascade delete)
- title: string (obrigatório)
- description: text (nullable)
- status: enum('pending', 'in_progress', 'completed') default 'pending'
- estimated_pomodoros: integer default 1
- completed_pomodoros: integer default 0
- timestamps

Relacionamentos:
- belongsTo(User::class)                    // Tarefa pertence a um usuário
- hasMany(PomodoroSession::class)           // Tarefa tem muitas sessões Pomodoro

Fillable:
['user_id', 'title', 'description', 'status', 'estimated_pomodoros', 'completed_pomodoros']
```

### **3. PomodoroSession (Sessão Pomodoro)**

```php
// Model: app/Models/PomodoroSession.php
// Migrations:
//   - 2024_01_01_000002_create_pomodoro_sessions_table.php
//   - 2025_11_15_000000_add_pause_fields_to_pomodoro_sessions.php

Schema:
- id: bigint (PK)
- user_id: bigint (FK → users.id, cascade delete)
- task_id: bigint (FK → tasks.id, cascade delete)
- duration: integer default 25 (minutos)
- start_time: timestamp
- end_time: timestamp (nullable)
- status: enum('active', 'completed', 'cancelled') default 'active'
- is_paused: boolean default false             // Adicionado em 2025
- remaining_seconds: integer (nullable)        // Adicionado em 2025
- timestamps

Relacionamentos:
- belongsTo(User::class)       // Sessão pertence a um usuário
- belongsTo(Task::class)       // Sessão pertence a uma tarefa

Fillable:
['user_id', 'task_id', 'duration', 'start_time', 'end_time', 'status', 'is_paused', 'remaining_seconds']

Casts:
- start_time → datetime
- end_time → datetime
```

### **Diagrama de Relacionamentos**

```
┌─────────────┐
│    User     │
│  (users)    │
└──────┬──────┘
       │
       │ 1:N
       ├──────────────┐
       │              │
       ▼              ▼
┌─────────────┐ ┌──────────────────┐
│    Task     │ │ PomodoroSession  │
│  (tasks)    │ │ (pomodoro_       │
│             │ │  sessions)       │
└──────┬──────┘ └──────────────────┘
       │              ▲
       │ 1:N          │
       └──────────────┘
```

---

## ⚙️ **Regras de Negócio**

### **1. Gestão de Tarefas**

#### **Criação de Tarefa**
- **Campos obrigatórios:**
  - `title` (máx 255 caracteres)
  - `estimated_pomodoros` (inteiro ≥ 1)
- **Campos opcionais:**
  - `description` (texto longo)
- **Defaults:**
  - `status = 'pending'`
  - `completed_pomodoros = 0`
  - `user_id = Auth::id()` (usuário logado)

#### **Atualização de Tarefa**
- **Validações:**
  - `title`: required, string, max:255
  - `description`: nullable, string
  - `status`: required, in:['pending', 'in_progress', 'completed']
  - `estimated_pomodoros`: required, integer, min:1
  - `completed_pomodoros`: nullable, integer, min:0

- **Regra especial:** Se `completed_pomodoros >= estimated_pomodoros`, status deve ser `'completed'`
- **Autorização:** Apenas o dono da tarefa pode atualizar (via `TaskPolicy`)

#### **Exclusão de Tarefa**
- **Cascade delete:** Ao deletar uma tarefa, todas as sessões Pomodoro associadas são deletadas automaticamente (constraint FK)
- **Autorização:** Apenas o dono pode deletar

#### **Agrupamento por Status (Frontend)**
Na view `tasks/index.blade.php`, tarefas são agrupadas em:
1. **Pendentes** (`status = 'pending'` E `completed_pomodoros < estimated_pomodoros`)
2. **Em Progresso** (`status = 'in_progress'` E `completed_pomodoros < estimated_pomodoros`)
3. **Concluídas** (`status = 'completed'` OU `completed_pomodoros >= estimated_pomodoros`)

---

### **2. Sessões Pomodoro**

#### **Iniciar Sessão (`POST /tasks/{task}/start-session`)**
**Fluxo:**
1. Cancelar qualquer sessão ativa anterior do usuário:
   ```php
   PomodoroSession::where('user_id', Auth::id())
       ->where('status', 'active')
       ->update(['status' => 'cancelled']);
   ```

2. Criar nova sessão:
   - `user_id = Auth::id()`
   - `task_id = {task->id}`
   - `duration = 25` minutos (fixo)
   - `start_time = now()`
   - `status = 'active'`
   - `is_paused = false`
   - `remaining_seconds = null`

3. Retornar JSON com sessão criada

**Autorização:** Usuário deve ser dono da tarefa (`TaskPolicy::view`)

---

#### **Pausar Sessão (`POST /sessions/{session}/pause`)**
**Validações:**
- Sessão deve estar `status = 'active'`
- Usuário deve ser dono da sessão (`PomodoroSessionPolicy::update`)

**Ação:**
```php
$session->update([
    'is_paused' => true,
    'remaining_seconds' => (int) $request->input('remaining_seconds')
]);
```

**Frontend:** Envia `remaining_seconds` calculado pelo JavaScript local

---

#### **Retomar Sessão (`POST /sessions/{session}/resume`)**
**Validações:**
- Sessão deve estar `is_paused = true`
- Usuário deve ser dono

**Ação:**
```php
$session->update([
    'is_paused' => false,
    'status' => 'active',
    'start_time' => now(),
    'remaining_seconds' => (int) $remaining_seconds_from_request
]);
```

**Frontend:** Recalcula o tempo restante baseado em `remaining_seconds`

---

#### **Completar Sessão (`POST /sessions/{session}/complete`)**
**Ação:**
1. Atualizar sessão:
   ```php
   $session->update([
       'end_time' => now(),
       'status' => 'completed',
       'is_paused' => false,
       'remaining_seconds' => null
   ]);
   ```

2. **Incrementar contador da tarefa:**
   ```php
   $session->task->increment('completed_pomodoros');
   ```

3. Retornar `completed_pomodoros` atualizado no JSON

**Autorização:** Usuário deve ser dono da sessão

---

#### **Cancelar Sessão (`POST /sessions/{session}/cancel`)**
**Ação:**
```php
$session->update([
    'status' => 'cancelled',
    'end_time' => now(),
    'is_paused' => false,
    'remaining_seconds' => null
]);
```

**Importante:** NÃO incrementa `completed_pomodoros`

---

#### **Buscar Sessão Ativa (`GET /active-session`)**
**Retorno JSON:**
```json
{
  "active": { /* PomodoroSession ativa e não pausada */ },
  "paused": [ /* Array de sessões pausadas do usuário */ ]
}
```

**Lógica:**
- `active`: sessão com `status = 'active'` E `is_paused = false`
- `paused`: todas as sessões com `is_paused = true`

**Uso:** Frontend consulta este endpoint para sincronizar estado do timer

---

### **3. Sincronização Frontend ↔ Backend**

#### **Estratégia Híbrida**
1. **Polling (atual):** JavaScript consulta `/active-session` a cada 5 segundos
2. **SSE (planejado):** EventSource em `/timer/stream` para push em tempo real

#### **Store Global (JavaScript Legado)**
- **Arquivo:** `resources/js/timer-store.js`
- **Escopo:** `globalThis.timerStore`
- **Métodos:**
  - `get()`: retorna estado atual
  - `set(payload)`: atualiza com dados do backend (`{active, paused}`)
  - `tick()`: decrementa 1 segundo (usado no timer focado)
  - `subscribe(callback)`: registra listener para mudanças
  - `getPausedTimeForTask(taskId)`: retorna tempo restante de sessão pausada
  - `setPaused(taskId, remainingSeconds)`: marca sessão como pausada no store

#### **Timer Focado**
- **Arquivo:** `resources/js/timer.js`
- **Funções principais:**
  - `toggleTimer(taskId)`: play/pause local
  - `pauseSession()`: chama `POST /sessions/{id}/pause` e para ticking local
  - `resumePausedSession(sessionId)`: chama `POST /sessions/{id}/resume`
  - `completePomodoro(taskId)`: chama `POST /sessions/{id}/complete`
  - `skipPomodoro(taskId)`: modal de confirmação → chama complete sem esperar timer zerar
  - `finishTask(taskId)`: marca tarefa como concluída (`PUT /tasks/{id}`)

#### **Sincronização de Cards**
- **Arquivo:** `resources/js/task-cards.js`
- **Lógica:**
  - Cards com preview do timer atualizam via `timerStore`
  - A cada atualização da store (`timer-store-updated` event), cards recalculam display

---

## 🚀 **Controllers e Endpoints**

### **TaskController**

| Método | Rota | Ação | Retorno |
|--------|------|------|---------|
| `GET` | `/home` | `index()` | View com lista de tarefas do usuário |
| `GET` | `/tasks/create` | `create()` | View do formulário de criação |
| `POST` | `/tasks` | `store(Request)` | Cria tarefa → Redirect `/tasks` |
| `GET` | `/tasks/{task}` | `show(Task)` | View de detalhes (não usada atualmente) |
| `GET` | `/tasks/{task}/edit` | `edit(Task)` | View do formulário de edição |
| `PUT/PATCH` | `/tasks/{task}` | `update(Request, Task)` | Atualiza tarefa → JSON ou Redirect |
| `DELETE` | `/tasks/{task}` | `destroy(Task)` | Deleta tarefa → Redirect `/tasks` |
| `GET` | `/tasks/{task}/timer` | `showTimer(Task)` | View do timer focado Pomodoro |

**Validações (`store` e `update`):**
```php
[
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'status' => 'required|in:pending,in_progress,completed',
    'estimated_pomodoros' => 'required|integer|min:1',
    'completed_pomodoros' => 'nullable|integer|min:0'
]
```

**Autorização:**
- `show`, `edit`, `update`, `destroy`: requerem `TaskPolicy::view/update/delete`

---

### **PomodoroController**

| Método | Rota | Ação | Retorno |
|--------|------|------|---------|
| `POST` | `/tasks/{task}/start-session` | `startSession(Task)` | JSON com sessão criada |
| `POST` | `/sessions/{session}/complete` | `completeSession(PomodoroSession)` | JSON com contador atualizado |
| `POST` | `/sessions/{session}/cancel` | `cancelSession(PomodoroSession)` | JSON confirmação |
| `POST` | `/sessions/{session}/pause` | `pauseSession(Request, PomodoroSession)` | JSON com sessão pausada |
| `POST` | `/sessions/{session}/resume` | `resumeSession(Request, PomodoroSession)` | JSON com sessão retomada |
| `GET` | `/active-session` | `getActiveSession()` | JSON `{active, paused}` |

**Autorização:**
- Todas as rotas verificam `PomodoroSessionPolicy::update` ou `TaskPolicy::view`

---

## 🔐 **Autorizações e Políticas**

### **TaskPolicy**
```php
// app/Policies/TaskPolicy.php

public function view(User $user, Task $task) {
    return $user->id === $task->user_id;
}

public function update(User $user, Task $task) {
    return $user->id === $task->user_id;
}

public function delete(User $user, Task $task) {
    return $user->id === $task->user_id;
}
```

**Regra:** Usuário só pode ver/editar/deletar suas próprias tarefas.

---

### **PomodoroSessionPolicy**
```php
// app/Policies/PomodoroSessionPolicy.php

public function update(User $user, PomodoroSession $session) {
    return $user->id === $session->user_id;
}
```

**Regra:** Usuário só pode pausar/retomar/completar/cancelar suas próprias sessões.

---

### **Registro de Policies**
```php
// app/Providers/AppServiceProvider.php

use App\Models\Task;
use App\Policies\TaskPolicy;
use App\Models\PomodoroSession;
use App\Policies\PomodoroSessionPolicy;
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::policy(Task::class, TaskPolicy::class);
    Gate::policy(PomodoroSession::class, PomodoroSessionPolicy::class);
}
```

---

## 🎨 **Frontend e Integração**

### **Arquitetura Frontend (Blade + JavaScript)**

O projeto utiliza **Blade Templates** do Laravel para renderização server-side com **JavaScript vanilla** para interatividade.

#### **Componentes Blade Principais**

| Componente | Arquivo | Descrição |
|------------|---------|-----------|
| Layout Base | `layouts/app.blade.php` | Sidebar responsiva, navegação, logout |
| Task List | `tasks/index.blade.php` | Lista agrupada por status (Pendente/Em Progresso/Concluída) |
| Task Card | `tasks/components/task-card.blade.php` | Card reutilizável com checkbox, progresso, ações |
| Task Form | `tasks/create.blade.php` / `tasks/edit.blade.php` | Formulários de CRUD |
| Timer Focado | `timer/focused.blade.php` | Timer circular SVG com botões play/pause/skip |
| Auth Views | `auth/login.blade.php` / `auth/register.blade.php` | Laravel UI authentication |

#### **JavaScript Modules**

**1. `timer-store.js` (Store Global)**
```javascript
// Gerencia estado compartilhado do timer
globalThis.timerStore = {
  get(),              // Retorna { active, paused }
  set(payload),       // Atualiza com dados do backend
  tick(),             // Decrementa 1 segundo
  subscribe(fn),      // Listener para mudanças
  getPausedTimeForTask(taskId),
  setPaused(taskId, seconds)
}
```

**2. `timer.js` (Timer Focado)**
```javascript
// Lógica do timer circular na página /tasks/{id}/timer
toggleTimer(taskId)             // Play/pause
pauseSession()                  // POST /sessions/{id}/pause
resumePausedSession(sessionId)  // POST /sessions/{id}/resume
completePomodoro(taskId)        // POST /sessions/{id}/complete
skipPomodoro(taskId)            // Modal confirmação + complete
forceCompleteTask(taskId)       // PUT /tasks/{id} (marcar concluído)
```

**3. `task-cards.js` (Sincronização Cards)**
```javascript
// Atualiza preview do timer nos cards da lista
// Escuta evento 'timer-store-updated' do timerStore
// Renderiza mini-timer com tempo restante
```

#### **Blade UI Kit (Heroicons)**
```blade
<!-- Ícones SVG via componentes Blade -->
<x-heroicon-o-home class="w-5 h-5" />
<x-heroicon-o-clipboard-document-list class="w-5 h-5" />
<x-heroicon-o-play class="w-5 h-5" />
<x-heroicon-o-pause class="w-5 h-5" />
```

---

## 📦 **Configuração e Dependências**

### **Dependências Backend (composer.json)**
```json
{
  "require": {
    "php": "^8.2",
    "laravel/framework": "^12.0",
    "laravel/ui": "^4.0"
  }
}
```

### **Dependências Frontend (package.json)**
```json
{
  "dependencies": {
    "axios": "^1.13.2"
  },
  "devDependencies": {
    "vite": "^7.0.7",
    "tailwindcss": "^4.0.0",
    "laravel-vite-plugin": "^2.0.0"
  }
}
```

### **Comandos de Setup**
```bash
# 1. Instalar dependências PHP
composer install

# 2. Configurar .env
cp .env.example .env
php artisan key:generate

# 3. Criar banco de dados MySQL
# DB_DATABASE=zenfocos
# DB_USERNAME=root
# DB_PASSWORD=

# 4. Rodar migrações
php artisan migrate

# 5. Instalar dependências Node
npm install

# 6. Build assets (desenvolvimento)
npm run dev

# 7. Rodar servidor Laravel
php artisan serve
```

### **Rodar em Desenvolvimento**
Terminal 1:
```bash
php artisan serve
# Laravel em http://localhost:8000
```

Terminal 2:
```bash
npm run dev
# Vite HMR em http://localhost:5173
```

**Acessar:** http://localhost:8000

---

## 📊 **Fluxogramas de Negócio**

### **Fluxo: Criar e Executar Pomodoro**

```
┌──────────────────┐
│ Usuário cria     │
│ nova tarefa      │
│ (título + N      │
│ pomodoros)       │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│ Task salva no    │
│ banco com:       │
│ status=pending   │
│ completed=0      │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│ Usuário clica    │
│ "Ver Pomodoro"   │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│ Timer focado     │
│ exibe 25:00      │
│ + botão play     │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│ Clica play       │
│ → POST /tasks/   │
│   {id}/start-    │
│   session        │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│ Backend:         │
│ - Cancela sessão │
│   ativa anterior │
│ - Cria nova      │
│   session        │
│   (active)       │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│ Timer JavaScript │
│ conta 25min      │
│ (tick local)     │
└────────┬─────────┘
         │
         ├─── Pausar? ───┐
         │                │
         ▼                ▼
┌──────────────┐   ┌────────────────┐
│ Timer chega  │   │ POST /sessions/│
│ a 00:00      │   │ {id}/pause     │
│              │   │ (salva tempo   │
│              │   │  restante)     │
└──────┬───────┘   └────────┬───────┘
       │                    │
       ▼                    ▼
┌──────────────┐   ┌────────────────┐
│ POST         │   │ Pausado.       │
│ /sessions/   │   │ Retomar?       │
│ {id}/        │   │ → POST resume  │
│ complete     │   └────────────────┘
└──────┬───────┘
       │
       ▼
┌──────────────┐
│ Backend:     │
│ - session.   │
│   status=    │
│   completed  │
│ - task.      │
│   completed_ │
│   pomodoros++│
└──────┬───────┘
       │
       ▼
┌──────────────┐
│ Alerta:      │
│ "Pomodoro    │
│ concluído!"  │
│ Reload page  │
└──────────────┘
```

---

### **Fluxo: Pular Sessão (Skip)**

```
┌──────────────────┐
│ Timer rodando ou │
│ parado           │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│ Clica "Pular"    │
│ → Modal confirma │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│ Confirma skip    │
│ → JS busca       │
│   /active-       │
│   session        │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│ Se existe sessão:│
│ POST /sessions/  │
│ {id}/complete    │
│                  │
│ Se NÃO existe:   │
│ PUT /tasks/{id}  │
│ (incrementa      │
│  completed_      │
│  pomodoros)      │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│ Reload página    │
└──────────────────┘
```

---

## 🔄 **Próximas Evoluções Planejadas**

1. **Migração completa para React:**
   - Implementar hooks de API (`useTaskList`, `useTask`, `useSessions`)
   - Converter `TaskCard` e `TaskForm` para componentes React
   - Migrar timer focado para React com Zustand

2. **Real-time com SSE:**
   - Implementar `/timer/stream` (EventSource)
   - Substituir polling por push notifications

3. **Relatórios e Estatísticas:**
   - Dashboard com gráficos de pomodoros por dia/semana
   - Tempo médio de foco
   - Taxa de conclusão de tarefas

4. **Notificações:**
   - Notificações desktop quando pomodoro completar
   - Som customizável

5. **Break Timer:**
   - Timer de pausa (5 minutos curto / 15 minutos longo)
   - Ciclo completo: 4 pomodoros → pausa longa
