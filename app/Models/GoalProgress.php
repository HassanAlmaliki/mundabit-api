<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'completed_hours',
        'notes',
        'date',
    ];

    public function goal()
    {
        return $this->belongsTo(SingleGoal::class, 'goal_id');
    }
}
