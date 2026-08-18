<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SingleGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'total_hours',
        'total_days',
        'daily_hours_required',
        'progress_percentage',
        'lock_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function progress()
    {
        return $this->hasMany(GoalProgress::class, 'goal_id');
    }
}
