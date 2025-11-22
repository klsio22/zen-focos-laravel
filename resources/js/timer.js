(() => {
    const cfg = globalThis.__timerConfig || {};
    const taskId = cfg.taskId || null;
    let timerInterval = null;
    let isRunning = false;
    let secondsRemaining = 25 * 60;
    let activeSession = null;

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
        const disp = document.getElementById("timer-display");
        if (disp) disp.textContent = formatTime(seconds);
        const prog = document.getElementById("timer-progress");
        if (prog) {
            const circumference = 565.48;
            const offset = circumference - (circumference * percent) / 100;
            prog.style.strokeDashoffset = offset;
            if (seconds > 300) {
                prog.classList.remove("text-orange-500", "text-red-600");
                prog.classList.add("text-blue-600");
            } else if (seconds > 60) {
                prog.classList.remove("text-blue-600", "text-red-600");
                prog.classList.add("text-orange-500");
            } else {
                prog.classList.remove("text-blue-600", "text-orange-500");
                prog.classList.add("text-red-600");
            }
        }
    }

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
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    Accept: "application/json",
                },
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data.active && !data.active.is_paused) {
                handleActiveSession(data.active);
                return;
            }
            if (Array.isArray(data.paused) && data.paused.length > 0) {
                handlePausedSessions(data.paused);
            }
        } catch (err) {
            console.error("fetchActiveSessionAndInit error:", err);
        }
    }

    async function toggleTimer(id) {
        if (isRunning) {
            await pauseSession();
            return;
        }
        const hasExistingForThis =
            activeSession &&
            activeSession.task_id === id &&
            secondsRemaining < 25 * 60;
        if (hasExistingForThis) {
            await resumeExistingOrLocal();
            return;
        }
        await startNewSession(id);
    }

    async function pauseSession() {
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
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify({ remaining_seconds: secondsRemaining }),
            });
            if (!res.ok) throw new Error("Falha ao pausar sessão");
            activeSession.is_paused = true;
            activeSession.remaining_seconds = secondsRemaining;
        } catch (err) {
            console.error("pauseSession error:", err);
        }
    }

    async function resumeExistingOrLocal() {
        if (activeSession.is_paused) {
            await resumePausedSession(activeSession.id);
            return;
        }
        startTimer();
    }

    async function resumePausedSession(sessionId) {
        try {
            const res = await fetch(`/sessions/${sessionId}/resume`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
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
            console.error("resumePausedSession error:", err);
            startTimer();
        }
    }

    async function startNewSession(id) {
        try {
            const res = await fetch(`/tasks/${id}/start-session`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
            });
            if (!res.ok) throw new Error("Resposta inválida ao iniciar sessão");
            const data = await res.json();
            activeSession = data.session;
            const elapsed = Math.floor(
                (Date.now() - new Date(activeSession.start_time).getTime()) /
                    1000
            );
            secondsRemaining = Math.max(
                (activeSession.duration || 25) * 60 - elapsed,
                0
            );
            startTimer();
        } catch (error) {
            console.error("startNewSession error:", error);
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
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            secondsRemaining--;
            updateTimerDisplay(secondsRemaining);
            if (globalThis.timerStore) globalThis.timerStore.tick();
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
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
                Accept: "application/json",
            },
        })
            .then((response) => response.json())
            .then((data) => {
                const current = data?.active ?? null;
                if (current?.id) {
                    fetch(`/sessions/${current.id}/complete`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,
                            "Content-Type": "application/json",
                            Accept: "application/json",
                        },
                    })
                        .then((response) => response.json())
                        .then(() => {
                            alert("Pomodoro concluído! Parabéns!");
                            activeSession = null;
                            secondsRemaining = 25 * 60;
                            isRunning = false;
                            location.reload();
                        })
                        .catch((err) =>
                            console.error("completePomodoro error:", err)
                        );
                }
            })
            .catch((err) =>
                console.error("completePomodoro fetch error:", err)
            );
    }

    function skipPomodoro(id) {
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

    // Show complete modal and perform completion when confirmed
    globalThis.__completeTaskId = null;

    function showCompleteModal(taskIdParam) {
        globalThis.__completeTaskId = Number(taskIdParam);
        const modal = document.getElementById("complete-modal");
        if (modal) modal.classList.remove("hidden");
    }

    function hideCompleteModal() {
        const modal = document.getElementById("complete-modal");
        if (modal) modal.classList.add("hidden");
        globalThis.__completeTaskId = null;
    }

    async function performCompleteForTask(taskIdParam) {
        const taskIdNum = Number(taskIdParam);
        const home = globalThis.__homeUrl || "/tasks";
        try {
            const res = await fetch("/active-session", {
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    Accept: "application/json",
                },
            });

            if (!res.ok) throw new Error("Falha ao recuperar sessão ativa");
            const data = await res.json();
            const current = data?.active ?? null;
            let sessionToComplete = null;
            if (current && Number(current.task_id) === taskIdNum)
                sessionToComplete = current;
            else if (Array.isArray(data.paused) && data.paused.length) {
                const pausedForThis = data.paused.find(
                    (p) => Number(p.task_id) === taskIdNum
                );
                if (pausedForThis) sessionToComplete = pausedForThis;
            }

            if (sessionToComplete?.id) {
                const completeRes = await fetch(
                    `/sessions/${sessionToComplete.id}/complete`,
                    {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,
                            "Content-Type": "application/json",
                            Accept: "application/json",
                        },
                    }
                );
                if (!completeRes.ok)
                    throw new Error("Falha ao completar sessão");
            } else {
                const title =
                    document.querySelector("h1")?.textContent?.trim() || "";
                const estimated =
                    globalThis.__timerConfig?.estimatedPomodoros || 1;
                const completedNow =
                    (globalThis.__timerConfig?.completedPomodoros || 0) + 1;
                const newStatus =
                    completedNow >= estimated && estimated > 0
                        ? "completed"
                        : "pending";

                const taskRes = await fetch(`/tasks/${taskIdNum}`, {
                    method: "PUT",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        status: newStatus,
                        title: title,
                        description: "",
                        estimated_pomodoros: estimated,
                        completed_pomodoros: completedNow,
                    }),
                });

                if (!taskRes.ok) throw new Error("Falha ao atualizar tarefa");
            }

            // Success: hide modal and redirect home
            hideCompleteModal();
            globalThis.location.href = home;
        } catch (err) {
            console.error("performCompleteForTask error:", err);
            // show error message inside modal
            const msg = document.getElementById("complete-modal-message");
            if (msg)
                msg.textContent =
                    "Erro ao concluir tarefa: " + (err.message || err);
        }
    }

    // Exposed function: opens the confirmation modal
    globalThis.forceCompleteTask = function (taskIdParam) {
        showCompleteModal(taskIdParam);
    };

    globalThis.addEventListener("load", async () => {
        if (!document.getElementById("timer-display")) return;
        await fetchActiveSessionAndInit();
        updateTimerDisplay(secondsRemaining);
        const confirmBtn = document.getElementById("skip-confirm-btn");
        const cancelBtn = document.getElementById("skip-cancel-btn");
        const completeConfirmBtn = document.getElementById(
            "complete-confirm-btn"
        );
        const completeCancelBtn = document.getElementById(
            "complete-cancel-btn"
        );
        if (confirmBtn) {
            confirmBtn.addEventListener("click", async () => {
                try {
                    confirmBtn.disabled = true;
                    const res = await fetch("/active-session", {
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,
                            Accept: "application/json",
                        },
                    });
                    if (!res.ok)
                        throw new Error("Falha ao recuperar sessão ativa");
                    const data = await res.json();
                    const current = data?.active ?? null;
                    let sessionToComplete = null;
                    if (current && Number(current.task_id) === Number(taskId))
                        sessionToComplete = current;
                    else if (Array.isArray(data.paused) && data.paused.length) {
                        const pausedForThis = data.paused.find(
                            (p) => Number(p.task_id) === Number(taskId)
                        );
                        if (pausedForThis) sessionToComplete = pausedForThis;
                    }
                    if (sessionToComplete?.id) {
                        const completeRes = await fetch(
                            `/sessions/${sessionToComplete.id}/complete`,
                            {
                                method: "POST",
                                headers: {
                                    "X-CSRF-TOKEN": document.querySelector(
                                        'meta[name="csrf-token"]'
                                    ).content,
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                },
                            }
                        );
                        if (!completeRes.ok)
                            throw new Error("Falha ao pular sessão");
                    } else {
                        // No active/paused session: increment completed_pomodoros on task
                        try {
                            const title =
                                document
                                    .querySelector("h1")
                                    ?.textContent?.trim() || "";
                            const estimated =
                                (globalThis.__timerConfig &&
                                    globalThis.__timerConfig
                                        .estimatedPomodoros) ||
                                1;
                            const completedNow =
                                ((globalThis.__timerConfig &&
                                    globalThis.__timerConfig
                                        .completedPomodoros) ||
                                    0) + 1;
                            const newStatus =
                                completedNow >= estimated && estimated > 0
                                    ? "completed"
                                    : "pending";

                            const taskRes = await fetch(`/tasks/${taskId}`, {
                                method: "PUT",
                                headers: {
                                    "X-CSRF-TOKEN": document.querySelector(
                                        'meta[name="csrf-token"]'
                                    ).content,
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                },
                                body: JSON.stringify({
                                    status: newStatus,
                                    title: title,
                                    description: "",
                                    estimated_pomodoros: estimated,
                                    completed_pomodoros: completedNow,
                                }),
                            });

                            if (!taskRes.ok)
                                throw new Error("Falha ao atualizar tarefa");
                        } catch (err) {
                            console.error("skip fallback update error:", err);
                        }
                    }
                    clearInterval(timerInterval);
                    isRunning = false;
                    hideSkipModal();
                    location.reload();
                } catch (err) {
                    console.error("skip confirm error:", err);
                    alert("Erro ao pular pomodoro: " + (err.message || err));
                    hideSkipModal();
                } finally {
                    confirmBtn.disabled = false;
                }
            });
        }
        if (cancelBtn)
            cancelBtn.addEventListener("click", () => hideSkipModal());

        if (completeCancelBtn)
            completeCancelBtn.addEventListener("click", () => {
                const msg = document.getElementById("complete-modal-message");
                if (msg)
                    msg.textContent = "Deseja realmente concluir esta tarefa?";
                hideCompleteModal();
            });

        if (completeConfirmBtn)
            completeConfirmBtn.addEventListener("click", async () => {
                completeConfirmBtn.disabled = true;
                const taskIdToComplete = globalThis.__completeTaskId || taskId;
                await performCompleteForTask(taskIdToComplete);
                completeConfirmBtn.disabled = false;
            });

        if (globalThis.timerStore) {
            globalThis.timerStore.subscribe((storeState) => {
                if (storeState.taskId === taskId) {
                    secondsRemaining = storeState.remaining || 0;
                    updateTimerDisplay(secondsRemaining);
                    if (!isRunning && storeState.taskId !== null) startTimer();
                }
            });
        }

        globalThis.toggleTimer = toggleTimer;
        globalThis.skipPomodoro = skipPomodoro;
    });
})();
