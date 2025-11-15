# Sincronização em Tempo Real - Timer Store

## 🎯 Objetivo
Implementar sincronização em tempo real entre o card de pré-visualização do timer e a página do timer focado, compartilhando um único estado centralizado.

## 📋 Arquitetura

### 1. **Store Global (`resources/js/timer-store.js`)**
- **Propósito:** Centralizar o estado do timer em `window.timerStore`
- **API Principal:**
  - `get()` - Obter estado atual
  - `set(payload)` - Atualizar estado com dados do servidor
  - `tick()` - Decrementar segundos (durante ticking local)
  - `getPausedList()` - Obter lista de sessões pausadas
  - `getPausedTimeForTask(taskId)` - Obter tempo pausado de uma tarefa específica
  - `subscribe(callback)` - Escutar mudanças de estado
  - `reset()` - Resetar para estado padrão

- **Estado Armazenado:**
  ```javascript
  {
    taskId: null,           // ID da tarefa com sessão ativa
    duration: 25,          // Duração do pomodoro em minutos
    remaining: 1500,       // Segundos restantes
    isPaused: false,       // Se está pausado
    startTime: null,       // Timestamp de início
    pausedList: [],        // Array de sessões pausadas
  }
  ```

- **Evento:** `timer-store-updated` disparado em cada mudança (permite cross-component sync)

### 2. **Task Cards (`resources/js/task-cards.js`)**
- **Mudança Principal:** Remover polling direto; usar `window.timerStore` como fonte de verdade
- **Fluxo:**
  1. Função `updateStoreFromServer()` busca dados via `/active-session`
  2. Dados são passados para `window.timerStore.set(data)`
  3. Store dispara evento `timer-store-updated`
  4. Listener de cada card reage e atualiza seu display independentemente
  5. Cada card trata seu estado: ativo (ticking), pausado (estático) ou inativo (preview)

- **Vantagem:** Cada card se atualiza **sem fazer fetch próprio**, apenas reagindo ao estado centralizado

### 3. **Timer Focado (`resources/js/timer.js`)**
- **Mudança Principal:** Adicionar listener da store para sincronização da página
- **Fluxo:**
  1. Quando usuário clica "Iniciar", inicia ticking local (já existente)
  2. Durante ticking, chama `window.timerStore.tick()` para atualizar estado central
  3. Listener escuta `timer-store-updated` (vindo de cards ou SSE)
  4. Se a tarefa desta página está ativa no store, sincroniza `secondsRemaining`
  5. UI da página se atualiza sempre que há mudança no store

- **Resultado:** Página de timer e cards sempre mostram o mesmo tempo, sem atrasos

## 🔄 Fluxo de Sincronização

```
┌─────────────────────────────────────────────────────────────┐
│                   window.timerStore                         │
│  (centraliza state: taskId, remaining, pausedList, etc)     │
└──────────────┬──────────────────────┬──────────────────────┘
               │                      │
        ┌──────▼────────┐      ┌──────▼────────┐
        │  task-cards   │      │   timer.js    │
        │  (cards list) │      │ (focused page)│
        └───┬──────┬────┘      └───┬──────┬────┘
            │      │               │      │
    ┌───────▼──┐  │        ┌──────▼──┐   │
    │Polling   │  │        │ Ticking │   │
    │(5s)      │  │        │(1s)     │   │
    └───────┬──┘  │        └──────┬──┘   │
            │     │               │      │
            │     └─ subscribe ───┤      │
            │                     │      │
            └──── emit: timer-store-updated ─────┘
```

## ⚙️ Alterações de Código

### `resources/js/timer-store.js` (NOVO)
- Arquivo centralizado que define `window.timerStore`
- Gerencia estado único para toda a aplicação
- Permite que qualquer módulo (cards, timer) acesse/ouça mudanças

### `resources/js/app.js` (MODIFICADO)
```javascript
import './bootstrap';
import './timer-store';  // ← Adicionar PRIMEIRO
import './task-cards';
```
Garante que a store está disponível antes de task-cards precisar.

### `resources/js/task-cards.js` (REFATORADO)
- Removeu polling direto e lógica de sincronização complexa
- Adicionou `updateStoreFromServer()` para buscar e atualizar store
- Cada card agora escuta `timer-store-updated` e se atualiza independentemente
- Mantém ticking local para cards com sessão ativa

### `resources/js/timer.js` (MELHORADO)
- `startTimer()` agora chama `window.timerStore.tick()` durante ticking
- Adicionado listener para `timer-store-updated` (subscribe)
- Sincroniza `secondsRemaining` quando tarefa desta página está ativa no store
- Auto-inicia timer se estava pausado e volta a estar ativo

### `resources/views/timer/focused.blade.php` (MODIFICADO)
```blade
@vite(['resources/js/timer-store.js', 'resources/js/timer.js'])
```
Garante que timer-store carrega antes de timer.js na página focada.

## ✅ Casos de Uso

### Caso 1: Usuário inicia timer via card
1. Usuário navega para `/tasks` (cards são carregadas)
2. Clica "Ver Pomodoro" em um card
3. Vai para `/tasks/{id}/timer` (página focada carrega)
4. Clica "Iniciar"
5. **Resultado:** Timer ticking local; store.tick() é chamado; evento dispara; card atualiza em tempo real

### Caso 2: Múltiplos tabs/janelas
1. Tab 1: Lista de cards (tasks)
2. Tab 2: Página de timer focado
3. Usuário inicia timer na Tab 2
4. **Resultado:** Ambos os tabs veem o timer decrementando em sincronização (via polling)

### Caso 3: Pausa e retoma
1. Timer rodando, card e página síncronos
2. Usuário pausa
3. **Resultado:** Store salva `remaining_seconds`; card mostra tempo pausado; página mostra tempo pausado

## 🚀 Como Testar

### 1. Setup Inicial
```bash
# Terminal 1: Build assets
npm run dev

# Terminal 2: Iniciar servidor Laravel
php artisan serve

# Terminal 3 (opcional): Ver logs
tail -f storage/logs/laravel.log
```

### 2. Teste Manual
1. Abra `http://localhost:8000/home` em uma aba (cards)
2. Abra `http://localhost:8000/tasks/{taskId}/timer` em outra aba (timer focado)
3. Inicie o timer via "Iniciar"
4. Verifique:
   - ✅ Ambas as abas decrementam em sincronismo (a cada ~1 segundo)
   - ✅ Ao pausar em uma aba, ambas pausam
   - ✅ Tempo pausado é exibido nas abas corretamente
   - ✅ Ao retomar, ambas continuam juntas

### 3. Verificar Console do Navegador
```javascript
// Abra DevTools (F12) → Console
window.timerStore.get()  // Ver estado atual
window.timerStore.getPausedList()  // Ver sessões pausadas

// Simular listener
window.timerStore.subscribe((state) => {
  console.log('State updated:', state);
});
```

## 📊 Diagrama de Estado

```
┌─────────────────────────────────────────────┐
│         Sem Sessão Ativa                    │
│  taskId: null, remaining: 1500, isPaused: false
│  → Mostrar preview 25:00 em todos os cards  │
└─────────────────────────────────────────────┘
                    ↓ (usuário clica "Iniciar")
┌─────────────────────────────────────────────┐
│      Sessão Ativa (Ticking)                 │
│  taskId: 123, remaining: 1499, isPaused: false
│  → Card 123: ticking                         │
│  → Outros cards: hidden                     │
│  → Página do timer: ticking                 │
└─────────────────────────────────────────────┘
              ↓ (usuário clica "Pausar")
┌─────────────────────────────────────────────┐
│      Sessão Pausada                         │
│  taskId: null, pausedList: [{task_id: 123, remaining_seconds: 1200}]
│  → Card 123: mostrar 20:00 (estático)      │
│  → Página do timer: 20:00                   │
└─────────────────────────────────────────────┘
              ↓ (usuário clica "Retomar")
┌─────────────────────────────────────────────┐
│      Sessão Ativa Novamente                 │
│  taskId: 123, remaining: 1200, isPaused: false
│  → Continua ticking de onde parou           │
└─────────────────────────────────────────────┘
```

## 🔌 Integração com SSE (Futuro)

Quando implementar SSE (`/timer/stream`):
```javascript
// No TimerController.php:
const stream = new EventSource('/timer/stream');
stream.onmessage = (e) => {
  const data = JSON.parse(e.data);
  window.timerStore.set(data);  // ← Usa a mesma API!
};
```

Isso significa que SSE e polling podem coexistir, ambos atualizando a mesma store.

## 📝 Notas Importantes

1. **Timer-store.js deve carregar PRIMEIRO** para estar disponível globalmente
2. **Cada card é independente** - se um card recebe update, apenas aquele card se renderiza
3. **Ticking é local** (setTimeout de 1s), não faz requisições a cada tick
4. **Polling como fallback** continua a cada 5s para garantir sincronização mesmo sem SSE
5. **Store é compartilhada entre todas as janelas/abas** via polling (não WebSocket real, apenas o estado)

## 🐛 Possíveis Problemas e Soluções

| Problema | Causa | Solução |
|----------|-------|---------|
| Store undefined | timer-store.js não carregou | Verificar ordem de imports em app.js |
| Card não atualiza | Subscriber não disparado | Verificar evento `timer-store-updated` no DevTools |
| Desincronização entre abas | SSE não configurado | Usar polling (5s) está funcionando, esperar menos diff |
| Pause não salva | Backend não possui campos | Rodar `php artisan migrate` |

## 🎓 Conclusão

Esta arquitetura garante:
- ✅ **Sincronização centralizada:** Uma única fonte de verdade
- ✅ **Componentes independentes:** Cards não conhecem uns aos outros
- ✅ **Escalável:** Fácil adicionar novos componentes que escutem a store
- ✅ **Sem complexidade adicional:** Reutiliza padrões JS simples (events, closures)
- ✅ **Compatível com SSE:** Pode ser escalado para real-time sem quebras
