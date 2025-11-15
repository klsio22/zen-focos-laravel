// task-cards.js
// JS extracted from resources/views/tasks/components/task-card.blade.php
// Responsible for polling active/paused sessions and updating per-card small timers

// Initialize task-card timers once per page
if (!globalThis.__taskCardTimerInit) {
  globalThis.__taskCardTimerInit = true;
  globalThis.__taskCardTimers = new Map(); // taskId -> intervalId

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

  // Start polling and syncing timers
  (function initPolling() {
    let activeSessionCached = null;

    async function sync() {
      const data = await fetchActiveSession();
      const active = data.active;
      const pausedList = data.paused || [];

      // find all cards
      const cards = document.querySelectorAll(".task-card");

      // If no active nor paused sessions, clear timers and hide previews
      if (!active && (!pausedList || pausedList.length === 0)) {
        cards.forEach((card) => {
          const tid = Number(card.dataset.taskId || 0);
          if (globalThis.__taskCardTimers.has(tid)) {
            clearInterval(globalThis.__taskCardTimers.get(tid));
            globalThis.__taskCardTimers.delete(tid);
          }
          hideCardTimer(card);
        });
        activeSessionCached = null;
        return;
      }

      // If we have an active session, compute elapsed and remaining
      let sessionTaskId = null;
      let duration = 25;
      let startTime = Date.now();
      if (active) {
        sessionTaskId = Number(active.task_id);
        duration = active.duration || 25;
        startTime = new Date(active.start_time).getTime();
      }

      cards.forEach((card) => {
        const tid = Number(card.dataset.taskId || 0);

        // If there is a paused session for this card, show paused preview without ticking
        const pausedForCard = pausedList.find((s) => Number(s.task_id) === tid);
        if (pausedForCard) {
          // show paused small timer using remaining_seconds if available
          showCardTimer(card);
          const remaining =
            typeof pausedForCard.remaining_seconds === "number"
              ? pausedForCard.remaining_seconds
              : (pausedForCard.duration || 25) * 60;
          updateCardTimerDisplay(card, remaining, pausedForCard.duration || 25);
          // clear any running interval
          if (globalThis.__taskCardTimers.has(tid)) {
            clearInterval(globalThis.__taskCardTimers.get(tid));
            globalThis.__taskCardTimers.delete(tid);
          }
          return;
        }

        if (active && tid === sessionTaskId) {
          // don't show timer for tasks that are already complete according to the card data
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
            if (globalThis.__taskCardTimers.has(tid)) {
              clearInterval(globalThis.__taskCardTimers.get(tid));
              globalThis.__taskCardTimers.delete(tid);
            }
            hideCardTimer(card);
            return;
          }

          // Show timer
          showCardTimer(card);

          // compute remaining seconds
          const elapsed = Math.floor((Date.now() - startTime) / 1000);
          let secondsRemaining = Math.max(duration * 60 - elapsed, 0);

          // If there's already an interval for this card, clear it first
          if (globalThis.__taskCardTimers.has(tid)) {
            clearInterval(globalThis.__taskCardTimers.get(tid));
            globalThis.__taskCardTimers.delete(tid);
          }

          // update immediately
          updateCardTimerDisplay(card, secondsRemaining, duration);

          // start ticking every second
          const intervalId = setInterval(() => {
            secondsRemaining = Math.max(secondsRemaining - 1, 0);
            updateCardTimerDisplay(card, secondsRemaining, duration);
            if (secondsRemaining <= 0) {
              clearInterval(intervalId);
              globalThis.__taskCardTimers.delete(tid);
              // hide when ended
              hideCardTimer(card);
            }
          }, 1000);

          globalThis.__taskCardTimers.set(tid, intervalId);
        } else {
          // Not the active task: ensure hidden and clear any interval
          if (globalThis.__taskCardTimers.has(tid)) {
            clearInterval(globalThis.__taskCardTimers.get(tid));
            globalThis.__taskCardTimers.delete(tid);
          }
          hideCardTimer(card);
        }
      });

      activeSessionCached = active;
    }

    // initial sync and then poll every 5 seconds
    sync();
    setInterval(sync, 5000);
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
