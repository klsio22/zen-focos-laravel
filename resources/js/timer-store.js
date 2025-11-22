// resources/js/timer-store.js
// Global timer state store used by timer and task cards.
// Manages active session, paused sessions and local state.

(() => {
  const STORE_KEY = '__timerState';
  const PAUSE_STORE_KEY = '__pausedTimers';

  const DEFAULT = {
    taskId: null,
    duration: 25,
    remaining: 25 * 60,
    isPaused: false,
    startTime: null,
    pausedList: [], // Array of paused session objects
  };

  // Helpers to save/load state
  const save = (s) => {
    window[STORE_KEY] = s;
  };

  const load = () => window[STORE_KEY] || { ...DEFAULT };

  const savePausedList = (list) => {
    window[PAUSE_STORE_KEY] = list;
  };

  const loadPausedList = () => window[PAUSE_STORE_KEY] || [];

  // ---- PUBLIC API ----
  globalThis.timerStore = {
    /**
     * Get current store state
     */
    get() {
      return load();
    },

    /**
     * Get paused sessions list
     */
    getPausedList() {
      return loadPausedList();
    },

    /**
     * Update store with new session data (called by SSE or polling)
     * @param {Object} payload - { active, paused }
     */
    set(payload) {
      const state = { ...DEFAULT };

      if (payload.active && !payload.active.is_paused) {
        // Active session in progress
        state.taskId = Number(payload.active.task_id);
        state.duration = payload.active.duration || 25;
        state.startTime = new Date(payload.active.start_time).getTime();

        // Calculate elapsed time since start
        const elapsed = Math.floor((Date.now() - state.startTime) / 1000);
        state.remaining = Math.max(state.duration * 60 - elapsed, 0);
        state.isPaused = false;
      } else {
        // No active session
        state.taskId = null;
        state.remaining = 25 * 60;
        state.isPaused = false;
      }
      // Save paused sessions list for cards
      if (Array.isArray(payload.paused) && payload.paused.length > 0) {
        state.pausedList = payload.paused;
        savePausedList(payload.paused);
      } else {
        state.pausedList = [];
        savePausedList([]);
      }

      save(state);

      // Dispatch event so listeners (cards, timer) update
      globalThis.dispatchEvent(
        new CustomEvent('timer-store-updated', { detail: state })
      );

      return state;
    },

    /**
     * Set paused state for a specific task
     * @param {number} taskId
     * @param {number} remainingSeconds
     */
    setPaused(taskId, remainingSeconds) {
      const state = load();
      state.taskId = null;
      state.isPaused = true;
      state.remaining = remainingSeconds;

      save(state);

      globalThis.dispatchEvent(
        new CustomEvent('timer-store-updated', { detail: state })
      );
    },

    /**
     * Called during local ticking (every 1s on focused timer page).
     * Decrements remaining and emits update.
     */
    tick() {
      const s = load();

      // Only tick if there is an active session and it is not paused
      if (s.taskId === null || s.isPaused) return;

      s.remaining = Math.max(s.remaining - 1, 0);
      save(s);

      globalThis.dispatchEvent(
        new CustomEvent('timer-store-updated', { detail: s })
      );
    },

    /**
     * Get remaining time for a paused task
     * @param {number} taskId
     * @returns {number|null} remaining seconds or null
     */
    getPausedTimeForTask(taskId) {
      const paused = loadPausedList();
      const session = paused.find((s) => Number(s.task_id) === Number(taskId));
      if (!session) return null;
      return session.remaining_seconds || (session.duration || 25) * 60;
    },

    /**
     * Reset store to default state
     */
    reset() {
      save({ ...DEFAULT });
      savePausedList([]);
      globalThis.dispatchEvent(
        new CustomEvent('timer-store-updated', { detail: { ...DEFAULT } })
      );
    },

    /**
     * Subscribe to store updates
     * @param {Function} callback
     */
    subscribe(callback) {
      globalThis.addEventListener('timer-store-updated', (e) => {
        callback(e.detail);
      });
    },
  };
})();
