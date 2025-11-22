(() => {
    const STORE_KEY = "__timerState";
    const PAUSE_STORE_KEY = "__pausedTimers";

    const DEFAULT = {
        taskId: null,
        duration: 25,
        remaining: 25 * 60,
        isPaused: false,
        startTime: null,
        pausedList: [],
    };

    const save = (s) => {
        window[STORE_KEY] = s;
    };
    const load = () => window[STORE_KEY] || { ...DEFAULT };
    const savePausedList = (list) => {
        window[PAUSE_STORE_KEY] = list;
    };
    const loadPausedList = () => window[PAUSE_STORE_KEY] || [];

    globalThis.timerStore = {
        get() {
            return load();
        },
        getPausedList() {
            return loadPausedList();
        },

        set(payload) {
            const state = { ...DEFAULT };
            if (payload.active && !payload.active.is_paused) {
                state.taskId = Number(payload.active.task_id);
                state.duration = payload.active.duration || 25;
                state.startTime = new Date(payload.active.start_time).getTime();
                const elapsed = Math.floor(
                    (Date.now() - state.startTime) / 1000
                );
                state.remaining = Math.max(state.duration * 60 - elapsed, 0);
                state.isPaused = false;
            } else {
                state.taskId = null;
                state.remaining = 25 * 60;
                state.isPaused = false;
            }

            if (Array.isArray(payload.paused) && payload.paused.length > 0) {
                state.pausedList = payload.paused;
                savePausedList(payload.paused);
            } else {
                state.pausedList = [];
                savePausedList([]);
            }

            save(state);
            globalThis.dispatchEvent(
                new CustomEvent("timer-store-updated", { detail: state })
            );
            return state;
        },

        setPaused(taskId, remainingSeconds) {
            const state = load();
            state.taskId = null;
            state.isPaused = true;
            state.remaining = remainingSeconds;
            save(state);
            globalThis.dispatchEvent(
                new CustomEvent("timer-store-updated", { detail: state })
            );
        },

        tick() {
            const s = load();
            if (s.taskId === null || s.isPaused) return;
            s.remaining = Math.max(s.remaining - 1, 0);
            save(s);
            globalThis.dispatchEvent(
                new CustomEvent("timer-store-updated", { detail: s })
            );
        },

        getPausedTimeForTask(taskId) {
            const paused = loadPausedList();
            const session = paused.find(
                (s) => Number(s.task_id) === Number(taskId)
            );
            if (!session) return null;
            return session.remaining_seconds || (session.duration || 25) * 60;
        },

        reset() {
            save({ ...DEFAULT });
            savePausedList([]);
            globalThis.dispatchEvent(
                new CustomEvent("timer-store-updated", {
                    detail: { ...DEFAULT },
                })
            );
        },

        subscribe(callback) {
            globalThis.addEventListener("timer-store-updated", (e) => {
                callback(e.detail);
            });
        },
    };
})();
