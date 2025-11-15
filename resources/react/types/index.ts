export interface Task {
  id: number;
  user_id: number;
  title: string;
  description: string | null;
  status: 'pending' | 'in_progress' | 'completed';
  estimated_pomodoros: number;
  completed_pomodoros: number;
  created_at: string;
  updated_at: string;
}

export interface PomodoroSession {
  id: number;
  task_id: number;
  user_id: number;
  start_time: string;
  duration: number; // in minutes
  is_paused: boolean;
  remaining_seconds?: number;
  created_at: string;
  updated_at: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
  };
}
