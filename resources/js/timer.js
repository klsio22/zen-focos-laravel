// timer.js
// JS extraído de resources/views/timer/focused.blade.php
// Expects a global `window.__timerConfig = { taskId, estimatedPomodoros, completedPomodoros }` to be set before this module loads.

(function () {
  const cfg = globalThis.__timerConfig || {};
  const taskId = cfg.taskId || null;
  let timerInterval = null;
  let isRunning = false;
  let secondsRemaining = 25 * 60;
  let activeSession = null; // will hold session data from backend if any

  function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, "0")}:${secs
      .toString()
      .padStart(2, "0")}`;
  }

  function updateTimerDisplay(seconds) {
    const totalSeconds = 25 * 60;
    const percent = (seconds / totalSeconds) * 100;

    // Atualizar texto
    const disp = document.getElementById("timer-display");
    if (disp) disp.textContent = formatTime(seconds);

    // Atualizar círculo
    const prog = document.getElementById("timer-progress");
    if (prog) {
      const circumference = 565.48; // 2 * π * 90
      const offset = circumference - (circumference * percent) / 100;
      prog.style.strokeDashoffset = offset;

      // Mudar cor baseado no tempo
      if (seconds > 300) {
        // > 5 minutos
        prog.classList.remove("text-orange-500", "text-red-600");
        prog.classList.add("text-blue-600");
      } else if (seconds > 60) {
        // 1-5 minutos
        prog.classList.remove("text-blue-600", "text-red-600");
        prog.classList.add("text-orange-500");
      } else {
        // < 1 minuto
        prog.classList.remove("text-blue-600", "text-orange-500");
        prog.classList.add("text-red-600");
      }
    }
  }

  // Fetch active / paused sessions on load and resume timer accordingly
  function handlePausedSessions(pausedArray) {
    const pausedForThis = pausedArray.find(
      (s) => Number(s.task_id) === Number(taskId)
    );
    if (!pausedForThis) return;

    activeSession = pausedForThis;
    if (typeof activeSession.remaining_seconds === "number") {
      secondsRemaining = activeSession.remaining_seconds;
      updateTimerDisplay(secondsRemaining);
    }
    // do not auto-start if paused
  }

  function handleActiveSession(session) {
    activeSession = session;
    if (activeSession.task_id !== taskId) return;

    const elapsed = Math.floor(
      (Date.now() - new Date(activeSession.start_time).getTime()) / 1000
    );
    secondsRemaining = Math.max(
      (activeSession.duration || 25) * 60 - elapsed,
      0
    );

    if (secondsRemaining <= 0) {
      completePomodoro(taskId);
      return;
    }

    startTimer();
  }

  async function fetchActiveSessionAndInit() {
    try {
      const res = await fetch("/active-session", {
        headers: {
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
            .content,
          Accept: "application/json",
        },
      });

      if (!res.ok) return;
      const data = await res.json();

      // Prefer active session if present and not paused
      if (data.active && !data.active.is_paused) {
        handleActiveSession(data.active);
        return;
      }

      // If there's a paused session for this task, use it to show paused state
      if (Array.isArray(data.paused) && data.paused.length > 0) {
        handlePausedSessions(data.paused);
      }
    } catch (err) {
      console.error("Erro ao buscar sessão ativa:", err);
    }
  }

  async function toggleTimer(id) {
    // keep the top-level function linear by delegating flows to small helpers
    if (isRunning) {
      await pauseSession();
      return;
    }

    const hasExistingForThis = activeSession && activeSession.task_id === id && secondsRemaining < 25 * 60;
    if (hasExistingForThis) {
      await resumeExistingOrLocal();
      return;
    }

    await startNewSession(id);
  }

  async function pauseSession() {
    // Pausar: parar tick local e persistir estado pausado no servidor
    const btnText = document.getElementById("btn-text");
    const btnPlayIcon = document.getElementById("btn-icon-play");
    const btnPauseIcon = document.getElementById("btn-icon-pause");

    clearInterval(timerInterval);
    isRunning = false;
    if (btnPlayIcon) btnPlayIcon.classList.remove("hidden");
    if (btnPauseIcon) btnPauseIcon.classList.add("hidden");
    if (btnText) btnText.textContent = "Retomar";

    if (!activeSession?.id) return;

    try {
      const res = await fetch(`/sessions/${activeSession.id}/pause`, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({ remaining_seconds: secondsRemaining }),
      });

      if (!res.ok) throw new Error('Falha ao pausar sessão');
      // update local snapshot
      activeSession.is_paused = true;
      activeSession.remaining_seconds = secondsRemaining;
    } catch (err) {
      console.error("Erro ao pausar sessão:", err);
    }
  }

  async function resumeExistingOrLocal() {
    // If the session is paused on the backend, try to resume via API; otherwise start locally
    if (activeSession.is_paused) {
      await resumePausedSession(activeSession.id);
      return;
    }

    // not paused -> just resume locally
    startTimer();
  }

  async function resumePausedSession(sessionId) {
    try {
      const res = await fetch(`/sessions/${sessionId}/resume`, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({ remaining_seconds: secondsRemaining }),
      });

      if (!res.ok) throw new Error("Falha ao retomar sessão");
      const data = await res.json();
      activeSession = data.session || activeSession;
      startTimer();
    } catch (err) {
      console.error("Erro ao retomar sessão:", err);
      // fallback to local start
      startTimer();
    }
  }

  async function startNewSession(id) {
    try {
      const res = await fetch(`/tasks/${id}/start-session`, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          "Content-Type": "application/json",
          Accept: "application/json",
        },
      });

      if (!res.ok) throw new Error("Resposta inválida ao iniciar sessão");

      const data = await res.json();
      activeSession = data.session;
      console.log("Sessão iniciada:", activeSession);
      const elapsed = Math.floor((Date.now() - new Date(activeSession.start_time).getTime()) / 1000);
      secondsRemaining = Math.max((activeSession.duration || 25) * 60 - elapsed, 0);
      startTimer();
    } catch (error) {
      console.error("Erro:", error);
      alert("Erro ao iniciar sessão");
    }
  }

  function startTimer() {
    isRunning = true;
    const btnPlayIcon = document.getElementById("btn-icon-play");
    const btnPauseIcon = document.getElementById("btn-icon-pause");
    const btnText = document.getElementById("btn-text");

    if (btnPlayIcon) btnPlayIcon.classList.add("hidden");
    if (btnPauseIcon) btnPauseIcon.classList.remove("hidden");
    if (btnText) btnText.textContent = "Pausar";

    timerInterval = setInterval(() => {
      secondsRemaining--;
      updateTimerDisplay(secondsRemaining);

      if (secondsRemaining <= 0) {
        clearInterval(timerInterval);
        isRunning = false;
        completePomodoro(taskId);
      }
    }, 1000);
  }

  function completePomodoro(id) {
    playNotificationSound();

    fetch("/active-session", {
      headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
          .content,
        Accept: "application/json",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        const current = data.active || null;
        if (current?.id) {
          fetch(`/sessions/${current.id}/complete`, {
            method: "POST",
            headers: {
              "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
              "Content-Type": "application/json",
              Accept: "application/json",
            },
          })
            .then((response) => response.json())
            .then((result) => {
              alert("Pomodoro concluído! Parabéns!");
              activeSession = null;
              secondsRemaining = 25 * 60;
              isRunning = false;
              location.reload();
            })
            .catch((err) => console.error("Erro ao completar:", err));
        }
      })
      .catch((err) => console.error("Erro ao buscar sessão:", err));
  }

  function skipPomodoro(id) {
    // Open the modal instead of native confirm
    showSkipModal(id);
  }

  function showSkipModal(id) {
    const modal = document.getElementById("skip-modal");
    if (modal) modal.classList.remove("hidden");
  }

  function hideSkipModal() {
    const modal = document.getElementById("skip-modal");
    if (modal) modal.classList.add("hidden");
  }

  function playNotificationSound() {
    const audioContext = new (globalThis.AudioContext ||
      globalThis.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    oscillator.frequency.value = 800;
    oscillator.type = "sine";

    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(
      0.01,
      audioContext.currentTime + 0.5
    );

    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.5);
  }

  // Inicializar: buscar sessão ativa, atualizar display e ligar handlers da modal
  window.addEventListener("load", async () => {
    // Only init if this module was loaded on the focused timer page
    if (!document.getElementById("timer-display")) return;

    await fetchActiveSessionAndInit();
    updateTimerDisplay(secondsRemaining);

    // Link modal buttons
    const confirmBtn = document.getElementById("skip-confirm-btn");
    const cancelBtn = document.getElementById("skip-cancel-btn");

    if (confirmBtn) {
      confirmBtn.addEventListener("click", () => {
        // perform skip action: clear timer and reload
        clearInterval(timerInterval);
        isRunning = false;
        hideSkipModal();
        // keep original behavior: reload to reflect changes
        location.reload();
      });
    }

    if (cancelBtn) {
      cancelBtn.addEventListener("click", () => {
        hideSkipModal();
      });
    }

    // Expose toggle/skip to global scope so markup can call onclick handlers
    globalThis.toggleTimer = toggleTimer;
    globalThis.skipPomodoro = skipPomodoro;
  });
})();
