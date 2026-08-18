<?php

namespace App\Services;

use App\Models\SingleGoal;

class GoalService
{
    /**
     * حساب عدد الساعات اليومية المطلوبة لتحقيق الهدف
     */
    public function calculateDailyHours(SingleGoal $goal)
    {
        if ($goal->total_days > 0) {
            $goal->daily_hours_required = $goal->total_hours / $goal->total_days;
        } else {
            $goal->daily_hours_required = 0;
        }
        $goal->save();
    }

    /**
     * حساب نسبة التقدم وتحديث حالة القفل
     */
    public function updateProgress(SingleGoal $goal)
    {
        $totalCompletedHours = $goal->progress()->sum('completed_hours');
        
        if ($goal->total_hours > 0) {
            $percentage = ($totalCompletedHours / $goal->total_hours) * 100;
            $goal->progress_percentage = min($percentage, 100);
        }

        // قفل الهدف (منع إضافة هدف جديد) يبقى مفعلاً حتى يتجاوز التقدم 90%
        if ($goal->progress_percentage >= 90) {
            $goal->lock_status = false; // فك القفل ليتمكن من إنهاء الهدف والبدء بغيره
        } else {
            $goal->lock_status = true;
        }

        $goal->save();
    }
}
