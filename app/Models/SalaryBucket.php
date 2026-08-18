<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryBucket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'limit_amount',
        'consumed_amount',
        'active_color',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'bucket_id');
    }
}
