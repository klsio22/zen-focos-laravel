# 🧪 Guia Prático de Teste - Sincronização em Tempo Real

## Pré-requisitos

- ✅ Node.js instalado (`npm --version`)
- ✅ PHP instalado (`php --version`)
- ✅ Laravel 12 configurado
- ✅ Database configurada e migrations rodadas

---

## 🚀 Passo 1: Setup Inicial

### Terminal 1 - Compilar Assets

```bash
cd /media/klsio27/outher-files/documentos/utfpr/projetos/zen-focos-laravel

npm run dev
```

**Você deve ver:**
```
VITE v5.x.x building for development...

✓ 1234 modules transformed.

➜  local:   http://localhost:5173/
➜  press h to show help
```

Deixe este terminal rodando.

### Terminal 2 - Servidor Laravel

```bash
cd /media/klsio27/outher-files/documentos/utfpr/projetos/zen-focos-laravel

php artisan serve
```

**Você deve ver:**
```
Laravel development server started on [http://127.0.0.1:8000]
```

---

## 🧪 Passo 2: Teste Prático

### Cenário 1: Sincronização Básica

1. **Abrir primeira aba**
   - URL: `http://localhost:8000/tasks`
   - Você verá a lista de tarefas com cards
   - Procure por um card com status "Pendente"

2. **Abrir segunda aba**
   - URL: `http://localhost:8000/tasks/{ID}/timer` (copie o ID da URL do card)
   - Exemplo: `http://localhost:8000/tasks/1/timer`

3. **Iniciar timer na Aba 2**
   - Clique no botão "Iniciar"
   - O timer começará a contar regressivamente de 25:00

4. **Verificar sincronização**
   - Volte para a Aba 1 (lista de cards)
   - Procure pelo card que você iniciou
   - ✅ **ESPERADO:** O card mostra o mesmo tempo que a Aba 2
   - ✅ **ESPERADO:** Ambos decrementam em sincronismo

5. **Verificar a cada segundo**
   - Aguarde 3-5 segundos
   - Verifique que ambas as abas mostram o MESMO tempo
   - **Diferença máxima esperada:** <1 segundo

---

### Cenário 2: Pause e Resume

1. **Com o timer rodando em ambas as abas**
   - Clique "Pausar" na Aba 2
   - ✅ **ESPERADO:** Ambas as abas param no mesmo tempo (ex: 24:55)
   - ✅ **ESPERADO:** Botão muda para "Retomar"

2. **Aguarde 3-5 segundos**
   - Aba 1 e Aba 2 continuam mostrando 24:55
   - ✅ **VERIFICADO:** Não decrementam enquanto pausado

3. **Clique "Retomar"**
   - ✅ **ESPERADO:** Ambas continuam contando de 24:55
   - ✅ **ESPERADO:** Sincronizadas novamente

---

### Cenário 3: Navegação Entre Abas

1. **Aba 1 (Cards):** Clique em "Ver Pomodoro" para card com sessão ativa
2. **Aba 2 abrirá** mostrando a página do timer
3. **Volte para Aba 1** (lista de cards)
4. ✅ **ESPERADO:** Card mostra preview do timer atualizado
5. **Volte para Aba 2** (página do timer)
6. ✅ **ESPERADO:** Página mostra o mesmo tempo que o card

---

### Cenário 4: Múltiplos Cards (Teste Avançado)

1. **Aba 1:** Existem 3+ cards de tarefas
2. **Inicie timer** em um deles
3. ✅ **ESPERADO:** Apenas aquele card mostra o timer (outros mostram 25:00 preview)
4. **Aba 2:** Abra a página do timer para o card iniciado
5. ✅ **VERIFICADO:** Páginas sincronizadas
6. **Volte para Aba 1**
7. ✅ **ESPERADO:** Todos os cards ainda mostram o preview correto

---

## 🔍 Console Debug

### Abrir DevTools (F12)

#### Aba Console

```javascript
// 1. Ver estado atual da store
window.timerStore.get()

// Resultado esperado:
{
  taskId: 1,
  remaining: 1495,        // segundos (25:00 = 1500)
  duration: 25,
  isPaused: false,
  startTime: 1731700000000,
  pausedList: []
}
```

```javascript
// 2. Ver lista de pausados (se houver)
window.timerStore.getPausedList()

// Resultado esperado quando pausado:
[
  {
    id: 10,
    task_id: 1,
    duration: 25,
    remaining_seconds: 1200,
    is_paused: true,
    // ... outros campos
  }
]
```

```javascript
// 3. Escutar TODAS as mudanças de estado
window.timerStore.subscribe(state => {
  console.log('🔄 STORE ATUALIZADA:', state)
})

// Agora cada mudança será logada no console
// Você verá atualizar a cada ~1 segundo durante ticking
```

#### Aba Network (Para Debugar Requisições)

1. Abra DevTools → Network
2. Iniciando timer, você verá:
   - `POST /tasks/{id}/start-session` (ao clicar "Iniciar")
   - `GET /active-session` (polling a cada 5 segundos)
   - Não deve ter muitas requisições (se tiver muitas, há problema)

---

## ❌ Possíveis Problemas e Soluções

### Problema 1: "Store is undefined"
```
Uncaught TypeError: window.timerStore is undefined
```

**Solução:**
- Verifique que `timer-store.js` foi carregado primeiro
- Cheque se `app.js` importa: `import './timer-store';`
- Recompile: `npm run dev`

---

### Problema 2: Timers Desincronizados

**Causa possível:** Browser cache

**Solução:**
```javascript
// Hard refresh em ambas as abas
Ctrl+Shift+R (ou Cmd+Shift+R no Mac)
```

---

### Problema 3: "Erro ao buscar sessão ativa"

**Causa possível:** Database sem migrations ou usuário não autenticado

**Solução:**
```bash
# Verifique login
http://localhost:8000/login

# Rode migrations se necessário
php artisan migrate

# Verifique logs
tail -f storage/logs/laravel.log
```

---

### Problema 4: Abas Não Sincronizam Absolutamente

**Causa possível:** Polling fora do sincronismo

**Solução:**
1. Verifique que ambas as abas estão ativas (não em background)
2. Abra DevTools e rode: `window.timerStore.get()`
3. Se não mudar por 5 segundos, há problema na fetch
4. Verifique Network tab para ver requests falhando

---

## 📊 Checklist de Validação Final

- [ ] Compilação via `npm run dev` sem erros
- [ ] Servidor Laravel rodando em `http://localhost:8000`
- [ ] Consigo logar e ver lista de tarefas
- [ ] Card mostra preview 25:00 por padrão
- [ ] Clico "Ver Pomodoro" em um card
- [ ] Página do timer abre corretamente
- [ ] Clico "Iniciar"
- [ ] Ambas as abas decrementam em sincronismo
- [ ] Clico "Pausar"
- [ ] Ambas as abas param no mesmo tempo
- [ ] Clico "Retomar"
- [ ] Ambas continuam contando juntas
- [ ] Console mostra evento `timer-store-updated` a cada segundo
- [ ] Diferença máxima entre abas: <1 segundo

---

## 📈 Métricas de Sucesso

| Métrica | Esperado | Teste |
|---------|----------|-------|
| Sincronização entre abas | <500ms | ✅ |
| Requests por segundo | <1 | Verificar Network |
| CPU usage | Estável | Verificar Task Manager |
| Memory | Estável (<50MB) | Verificar DevTools |

---

## 🎯 Conclusão do Teste

**Se todos os cenários acima funcionaram:** ✅ **Implementação bem-sucedida!**

**Próximos passos opcionais:**
- Implementar SSE para sincronização sub-segundo
- Adicionar notificações sonoras
- Testar em múltiplos navegadores/dispositivos

---

## 📞 Troubleshooting Rápido

| Erro | Solução |
|------|---------|
| `timer-store.js not found` | Verificar path, hard refresh |
| "Identifier already declared" | Duplicação de função (corrigida) |
| Desincronização de 5+ segundos | Polling interval, verificar rede |
| Console errors | F12 → Console, copiar erro completo |

---

**Boa sorte com os testes! 🚀**
