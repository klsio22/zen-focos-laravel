<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'estimated_pomodoros',
        'completed_pomodoros'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pomodoroSessions()
    {
        return $this->hasMany(PomodoroSession::class);
    }
}
