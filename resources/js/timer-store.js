// resources/js/timer-store.js
// Global store para sincronização de estado do timer entre componentes
// Gerencia: sessão ativa, sessões pausadas, e estado local para cada tarefa

(() => {
  const STORE_KEY = '__timerState';
  const PAUSE_STORE_KEY = '__pausedTimers';

  const DEFAULT = {
    taskId: null,
    duration: 25,
    remaining: 25 * 60,
    isPaused: false,
    startTime: null,
    pausedList: [], // Array de tasks com sessão pausada
  };

  // Helpers para salvar/carregar estado
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
        // Sessão ativa em andamento
        state.taskId = Number(payload.active.task_id);
        state.duration = payload.active.duration || 25;
        state.startTime = new Date(payload.active.start_time).getTime();

        // Calcular tempo decorrido desde início
        const elapsed = Math.floor((Date.now() - state.startTime) / 1000);
        state.remaining = Math.max(state.duration * 60 - elapsed, 0);
        state.isPaused = false;
      } else {
        // Sem sessão ativa
        state.taskId = null;
        state.remaining = 25 * 60;
        state.isPaused = false;
      }

      // Guardar lista de sessões pausadas para acesso por card individual
      if (Array.isArray(payload.paused) && payload.paused.length > 0) {
        state.pausedList = payload.paused;
        savePausedList(payload.paused);
      } else {
        state.pausedList = [];
        savePausedList([]);
      }

      save(state);

      // Disparar evento para que listeners (cards, timer) se atualizem
      globalThis.dispatchEvent(
        new CustomEvent('timer-store-updated', { detail: state })
      );

      return state;
    },

    /**
     * Atualizar estado pausado de uma tarefa específica
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
     * Chamar durante ticking local (a cada 1s no timer focused page)
     * Decrementa remaining e dispara evento
     */
    tick() {
      const s = load();

      // Só fazer tick se há sessão ativa E não está pausada
      if (s.taskId === null || s.isPaused) return;

      s.remaining = Math.max(s.remaining - 1, 0);
      save(s);

      globalThis.dispatchEvent(
        new CustomEvent('timer-store-updated', { detail: s })
      );
    },

    /**
     * Obter tempo restante para uma tarefa pausada específica
     * @param {number} taskId
     * @returns {number} remaining seconds or null
     */
    getPausedTimeForTask(taskId) {
      const paused = loadPausedList();
      const session = paused.find((s) => Number(s.task_id) === Number(taskId));
      if (!session) return null;
      return session.remaining_seconds || (session.duration || 25) * 60;
    },

    /**
     * Resetar store para estado padrão
     */
    reset() {
      save({ ...DEFAULT });
      savePausedList([]);
      globalThis.dispatchEvent(
        new CustomEvent('timer-store-updated', { detail: { ...DEFAULT } })
      );
    },

    /**
     * Adicionar listener para mudanças de estado
     * @param {Function} callback
     */
    subscribe(callback) {
      globalThis.addEventListener('timer-store-updated', (e) => {
        callback(e.detail);
      });
    },
  };
})();
