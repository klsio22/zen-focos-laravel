<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Task;
use App\Policies\TaskPolicy;
use App\Models\PomodoroSession;
use App\Policies\PomodoroSessionPolicy;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar Policies
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(PomodoroSession::class, PomodoroSessionPolicy::class);
    }
}
