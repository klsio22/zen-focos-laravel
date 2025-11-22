if (!globalThis.__taskCardTimerInit) {
    globalThis.__taskCardTimerInit = true;
    globalThis.__taskCardTimers = new Map();

    const nodeListForEach = (nodes, cb) =>
        Array.prototype.forEach.call(nodes, cb);

    async function fetchActiveSession() {
        try {
            const res = await fetch("/active-session", {
                headers: { Accept: "application/json" },
            });
            if (!res.ok) return { active: null, paused: [] };
            const data = await res.json();
            return { active: data.active || null, paused: data.paused || [] };
        } catch (e) {
            console.error("fetchActiveSession error:", e);
            return { active: null, paused: [] };
        }
    }

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

        const mins = Math.floor(secondsRemaining / 60);
        const secs = Math.max(secondsRemaining % 60, 0);
        if (timeEl)
            timeEl.textContent = `${String(mins).padStart(2, "0")}:${String(
                secs
            ).padStart(2, "0")}`;

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

    (function initPolling() {
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
                if (timeEl && (timeEl.textContent || "").trim() === "--:--")
                    timeEl.textContent = "25:00";
            });
        }

        function updateAllCardsFromStore(storeState) {
            const cards = document.querySelectorAll(".task-card");
            const pausedList = storeState.pausedList || [];
            nodeListForEach(cards, (card) => {
                const tid = Number(card.dataset.taskId || 0);
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

                if (storeState.taskId === tid) {
                    handleActiveCardFromStore(card, tid, storeState);
                    return;
                }

                const pausedForCard = pausedList.find(
                    (s) => Number(s.task_id) === tid
                );
                if (pausedForCard) {
                    handlePausedCardFromStore(card, tid, pausedForCard);
                    return;
                }

                handleInactiveCard(card, tid);
            });
        }

        function handleActiveCardFromStore(card, tid, storeState) {
            showCardTimer(card);
            if (globalThis.__taskCardTimers.has(tid)) {
                clearInterval(globalThis.__taskCardTimers.get(tid));
                globalThis.__taskCardTimers.delete(tid);
            }
            let secondsRemaining = storeState.remaining || 0;
            updateCardTimerDisplay(
                card,
                secondsRemaining,
                storeState.duration || 25
            );
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
            updateCardTimerDisplay(
                card,
                remaining,
                pausedForCard.duration || 25
            );
            if (globalThis.__taskCardTimers.has(tid)) {
                clearInterval(globalThis.__taskCardTimers.get(tid));
                globalThis.__taskCardTimers.delete(tid);
            }
        }

        function handleInactiveCard(card, tid) {
            if (globalThis.__taskCardTimers.has(tid)) {
                clearInterval(globalThis.__taskCardTimers.get(tid));
                globalThis.__taskCardTimers.delete(tid);
            }
            hideCardTimer(card);
            showCardTimer(card);
            updateCardTimerDisplay(card, 25 * 60, 25);
        }

        ensurePreviewVisibleForPending();
        updateStoreFromServer();

        if (globalThis.timerStore) {
            globalThis.timerStore.subscribe((storeState) => {
                updateAllCardsFromStore(storeState);
            });
        }

        setInterval(() => {
            updateStoreFromServer();
        }, 5000);
    })();
}

function computeNewCompleted(estimated, completed, isChecked) {
    if (isChecked) return estimated;
    return 0;
}

async function sendTaskUpdate(taskId, payload) {
    const res = await fetch(`/tasks/${taskId}`, {
        method: "PUT",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify(payload),
    });
    if (!res.ok) throw new Error(`HTTP error: ${res.status}`);
    return res.json();
}

async function cancelSessionById(sessionId) {
    return fetch(`/sessions/${sessionId}/cancel`, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
            Accept: "application/json",
        },
    });
}

async function cancelActiveOrPausedForTask(taskId) {
    try {
        const activeRes = await fetch("/active-session", {
            headers: { Accept: "application/json" },
        });
        if (!activeRes.ok) return;
        const activeData = await activeRes.json();
        const current = activeData.active || null;
        const pausedList = activeData.paused || [];
        if (current && Number(current.task_id) === Number(taskId)) {
            await cancelSessionById(current.id);
            return;
        }
        const pausedForTask = pausedList.find(
            (s) => Number(s.task_id) === Number(taskId)
        );
        if (pausedForTask) {
            await cancelSessionById(pausedForTask.id);
        }
    } catch (e) {
        console.error("cancelActiveOrPausedForTask error:", e);
    }
}

globalThis.updateTaskStatus = async function updateTaskStatus(
    taskId,
    isChecked,
    evt
) {
    const target = evt ? evt.target : null;
    const cardElement = target ? target.closest(".bg-white") : null;
    const titleElement = cardElement ? cardElement.querySelector("h3") : null;
    const title = titleElement ? titleElement.textContent.trim() : "";

    const estimated = target
        ? Number.parseInt(target.dataset.estimated || "0", 10)
        : 0;
    const completed = target
        ? Number.parseInt(target.dataset.completed || "0", 10)
        : 0;

    const newCompleted = computeNewCompleted(estimated, completed, isChecked);
    const newStatus =
        newCompleted >= estimated && estimated > 0 ? "completed" : "pending";

    try {
        await sendTaskUpdate(taskId, {
            status: newStatus,
            title: title,
            description: "",
            estimated_pomodoros: estimated || 1,
            completed_pomodoros: newCompleted,
        });
        if (target) target.dataset.completed = String(newCompleted);
        if (newStatus === "completed")
            await cancelActiveOrPausedForTask(taskId);
        setTimeout(() => location.reload(), 200);
    } catch (error) {
        console.error("updateTaskStatus error:", error);
        alert("❌ Erro ao atualizar status: " + (error.message || error));
    }
};
