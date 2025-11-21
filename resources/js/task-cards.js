// task-cards.js
// JS extracted from resources/views/tasks/components/task-card.blade.php
// Usa store global (window.timerStore) para sincronização em tempo real
// Cada card atualiza independentemente baseado no estado centralizado

// Initialize task-card timers once per page
if (!globalThis.__taskCardTimerInit) {
  globalThis.__taskCardTimerInit = true;
  globalThis.__taskCardTimers = new Map(); // taskId -> intervalId

  // Helper to iterate NodeList in environments where NodeList.forEach may not exist
  const nodeListForEach = (nodes, cb) =>
    Array.prototype.forEach.call(nodes, cb);

  async function fetchActiveSession() {
    try {
      const res = await fetch("/active-session", {
        headers: { Accept: "application/json" },
      });
      if (!res.ok) return { active: null, paused: [] };
      const data = await res.json();
      // Expecting { active: ..., paused: [...] }
      return { active: data.active || null, paused: data.paused || [] };
    } catch (e) {
      console.error("Erro ao buscar sessão ativa:", e);
      return { active: null, paused: [] };
    }
  }

  // Atualizar store global com dados obtidos
  async function updateStoreFromServer() {
    if (!window.timerStore) return;
    const data = await fetchActiveSession();
    window.timerStore.set(data);
  }

  function updateCardTimerDisplay(cardEl, secondsRemaining, duration) {
    const timerEl = cardEl.querySelector(".task-small-timer");
    if (!timerEl) return;
    const timeEl = timerEl.querySelector("[data-time]");
    const ring = cardEl.querySelector(".task-timer-ring");

    // format mm:ss
    const mins = Math.floor(secondsRemaining / 60);
    const secs = Math.max(secondsRemaining % 60, 0);
    if (timeEl)
      timeEl.textContent = `${String(mins).padStart(2, "0")}:${String(
        secs
      ).padStart(2, "0")}`;

    // update ring (circumference for r=44 is ~276.46)
    if (ring && typeof duration === "number" && duration > 0) {
      const total = duration * 60;
      const percent = Math.max(0, Math.min(1, secondsRemaining / total));
      const circumference = 276.46;
      const offset = circumference - circumference * percent;
      ring.style.strokeDashoffset = String(offset);
    }
  }

  function showCardTimer(cardEl) {
    const timerEl = cardEl.querySelector(".task-small-timer");
    if (timerEl) timerEl.classList.remove("hidden");
  }

  function hideCardTimer(cardEl) {
    const timerEl = cardEl.querySelector(".task-small-timer");
    if (timerEl) timerEl.classList.add("hidden");
  }

  // ========== INITIALIZE POLLING & STORE LISTENER ==========
  (function initPolling() {
    // Ensure pending cards show a preview immediately
    function ensurePreviewVisibleForPending() {
      const cards = document.querySelectorAll(".task-card");
      nodeListForEach(cards, (card) => {
        const cardEstimated = Number.parseInt(
          card.dataset.estimated || "0",
          10
        );
        const cardCompleted = Number.parseInt(
          card.dataset.completed || "0",
          10
        );
        const cardIsComplete =
          cardEstimated > 0 && cardCompleted >= cardEstimated;
        if (cardIsComplete) return;

        const timerEl = card.querySelector(".task-small-timer");
        if (!timerEl) return;

        timerEl.classList.remove("hidden");
        timerEl.style.display = timerEl.style.display || "flex";

        const timeEl = timerEl.querySelector("[data-time]");
        if (timeEl && (timeEl.textContent || "").trim() === "--:--") {
          timeEl.textContent = "25:00";
        }
      });
    }

    // Processar cada card individualmente baseado no estado da store
    function updateAllCardsFromStore(storeState) {
      const cards = document.querySelectorAll(".task-card");
      const pausedList = storeState.pausedList || [];

      nodeListForEach(cards, (card) => {
        const tid = Number(card.dataset.taskId || 0);

        // Verificar se é card completado
        const cardEstimated = Number.parseInt(
          card.dataset.estimated || "0",
          10
        );
        const cardCompleted = Number.parseInt(
          card.dataset.completed || "0",
          10
        );
        const cardIsComplete =
          cardEstimated > 0 && cardCompleted >= cardEstimated;

        if (cardIsComplete) {
          hideCardTimer(card);
          if (globalThis.__taskCardTimers.has(tid)) {
            clearInterval(globalThis.__taskCardTimers.get(tid));
            globalThis.__taskCardTimers.delete(tid);
          }
          return;
        }

        // Verificar se há sessão ativa para este card
        if (storeState.taskId === tid) {
          // Card com sessão ativa: fazer ticking
          handleActiveCardFromStore(card, tid, storeState);
          return;
        }

        // Verificar se há sessão pausada para este card
        const pausedForCard = pausedList.find((s) => Number(s.task_id) === tid);
        if (pausedForCard) {
          handlePausedCardFromStore(card, tid, pausedForCard);
          return;
        }

        // Card sem sessão ativa nem pausada: mostrar preview padrão
        handleInactiveCard(card, tid);
      });
    }

    function handleActiveCardFromStore(card, tid, storeState) {
      showCardTimer(card);

      // Se já há um interval para este card, limpar
      if (globalThis.__taskCardTimers.has(tid)) {
        clearInterval(globalThis.__taskCardTimers.get(tid));
        globalThis.__taskCardTimers.delete(tid);
      }

      // Usar remaining da store
      let secondsRemaining = storeState.remaining || 0;
      updateCardTimerDisplay(
        card,
        secondsRemaining,
        storeState.duration || 25
      );

      // Fazer ticking sincronizado com a store
      const intervalId = setInterval(() => {
        secondsRemaining = Math.max(secondsRemaining - 1, 0);
        updateCardTimerDisplay(
          card,
          secondsRemaining,
          storeState.duration || 25
        );

        if (secondsRemaining <= 0) {
          clearInterval(intervalId);
          globalThis.__taskCardTimers.delete(tid);
          hideCardTimer(card);
        }
      }, 1000);

      globalThis.__taskCardTimers.set(tid, intervalId);
    }

    function handlePausedCardFromStore(card, tid, pausedForCard) {
      showCardTimer(card);
      const remaining =
        typeof pausedForCard.remaining_seconds === "number"
          ? pausedForCard.remaining_seconds
          : (pausedForCard.duration || 25) * 60;
      updateCardTimerDisplay(card, remaining, pausedForCard.duration || 25);

      // Limpar qualquer interval para este card
      if (globalThis.__taskCardTimers.has(tid)) {
        clearInterval(globalThis.__taskCardTimers.get(tid));
        globalThis.__taskCardTimers.delete(tid);
      }
    }

    function handleInactiveCard(card, tid) {
      // Limpar interval se houver
      if (globalThis.__taskCardTimers.has(tid)) {
        clearInterval(globalThis.__taskCardTimers.get(tid));
        globalThis.__taskCardTimers.delete(tid);
      }

      // Mostrar preview padrão para cards pendentes
      hideCardTimer(card);
      showCardTimer(card);
      updateCardTimerDisplay(card, 25 * 60, 25);
    }

    // Initial setup: ensure preview visible
    ensurePreviewVisibleForPending();

    // Buscar estado inicial do servidor e atualizar store
    updateStoreFromServer();

    // Se store está disponível, subscribir a mudanças
    if (globalThis.timerStore) {
      globalThis.timerStore.subscribe((storeState) => {
        updateAllCardsFromStore(storeState);
      });
    }

    // Polling fallback: a cada 5 segundos buscar do servidor (para SSE ou conexões que caem)
    setInterval(() => {
      updateStoreFromServer();
    }, 5000);
  })();
}

// Expose the updateTaskStatus function globally so existing inline onchange handlers keep working
globalThis.updateTaskStatus = async function updateTaskStatus(
  taskId,
  isChecked,
  evt
) {
  // evt may be passed from the onchange handler
  const target = evt ? evt.target : null;
  const cardElement = target ? target.closest(".bg-white") : null;
  const titleElement = cardElement ? cardElement.querySelector("h3") : null;
  const title = titleElement ? titleElement.textContent.trim() : "";

  // read data attributes to compute new completed_pomodoros
  const estimated = target ? parseInt(target.dataset.estimated || "0", 10) : 0;
  const completed = target ? parseInt(target.dataset.completed || "0", 10) : 0;

  // If user checks the box, consider it fully completed (set completed = estimated)
  // If user unchecks, decrement completed by 1 (but not below 0) so it returns to previous progress
  let newCompleted = completed;
  if (isChecked) {
    newCompleted = estimated;
  } else {
    newCompleted = Math.max(completed - 1, 0);
  }

  const newStatus =
    newCompleted >= estimated && estimated > 0 ? "completed" : "pending";

  try {
    const response = await fetch(`/tasks/${taskId}`, {
      method: "PUT",
      headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
          .content,
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        status: newStatus,
        title: title,
        description: "",
        estimated_pomodoros: estimated || 1,
        completed_pomodoros: newCompleted,
      }),
    });

    if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);
    await response.json();

    // reflect change in UI quickly: update checkbox/data attribute
    if (target) {
      target.dataset.completed = String(newCompleted);
    }

    // If we just marked the task as completed, check for an active session and cancel it
    if (newStatus === "completed") {
      try {
        const activeRes = await fetch("/active-session", {
          headers: { Accept: "application/json" },
        });
        if (activeRes.ok) {
          const activeData = await activeRes.json();
          const current = activeData.active || null;
          const pausedList = activeData.paused || [];
          const pausedForTask = pausedList.find(
            (s) => Number(s.task_id) === Number(taskId)
          );
          // If there's an active session for this task or a paused session, cancel it
          if (current && Number(current.task_id) === Number(taskId)) {
            await fetch(`/sessions/${current.id}/cancel`, {
              method: "POST",
              headers: {
                "X-CSRF-TOKEN": document.querySelector(
                  'meta[name="csrf-token"]'
                ).content,
                Accept: "application/json",
              },
            });
          } else if (pausedForTask) {
            await fetch(`/sessions/${pausedForTask.id}/cancel`, {
              method: "POST",
              headers: {
                "X-CSRF-TOKEN": document.querySelector(
                  'meta[name="csrf-token"]'
                ).content,
                Accept: "application/json",
              },
            });
          }
        }
      } catch (e) {
        console.error("Erro ao cancelar sessão ativa:", e);
      }
    }

    // finally reload to reflect updated lists and counts
    setTimeout(() => location.reload(), 200);
  } catch (error) {
    console.error("Erro ao atualizar status:", error);
    alert("❌ Erro ao atualizar status: " + (error.message || error));
  }
};
