# ⚡ QUICK START - Sincronização em Tempo Real

## 🎯 5 Minutos para Começar

### Passo 1: Compilar (1 min)
```bash
npm run dev
```
Deixe rodando.

### Passo 2: Servidor (30 seg)
```bash
php artisan serve
```

### Passo 3: Abrir Navegador (1 min)

**Aba 1:**
```
http://localhost:8000/tasks
```

**Aba 2:**
```
http://localhost:8000/tasks/1/timer
```
(ou o ID da tarefa que deseja testar)

### Passo 4: Clicar "Iniciar" (1 min)

Em qualquer aba, clique o botão "Iniciar".

### Passo 5: Verificar Sincronização (1 min)

Olhe para ambas as abas. Elas devem mostrar o **mesmo tempo** decrementando juntas.

---

## ✅ Sucesso se...

✅ Ambas as abas mostram o mesmo tempo  
✅ Ambas decrementam juntas (cada 1 segundo)  
✅ Diferença máxima entre elas: <1 segundo  
✅ Pausa funciona em ambas  
✅ Retoma sincronizado em ambas  

---

## ❌ Problema se...

```
❌ Tempos diferentes
   → Hard refresh: Ctrl+Shift+R em ambas
   → Recompile: npm run dev

❌ Não decrementam
   → Verifique: http://localhost:8000/active-session
   → Se error, rode: php artisan migrate

❌ DevTools error
   → F12 → Console → copiar erro
   → Verificar se timer-store.js foi carregado
```

---

## 🧪 Teste no Console

```javascript
// Abra DevTools: F12 → Console

// 1. Ver estado
window.timerStore.get()

// 2. Monitorar mudanças
window.timerStore.subscribe(s => {
  console.log('⏱️ Remaining:', s.remaining, 's')
})

// 3. Ver pausados
window.timerStore.getPausedList()
```

---

## 📊 Resultado Esperado

```
Aba 1: Cards           Aba 2: Timer
═══════════════════════════════════════
24:55 (card)           24:55 (página)
24:54 (card)           24:54 (página)
24:53 (card)           24:53 (página)
...                    ...
SINCRONIZADO! ✅       SINCRONIZADO! ✅
```

---

## 🚀 Pronto!

Sua sincronização em tempo real está funcionando! 🎉

Para mais detalhes, veja:
- `TEST_GUIDE.md` - Testes completos
- `README_SYNC.md` - Visão geral
- `TIMER_STORE_README.md` - API da store
