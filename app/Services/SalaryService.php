<?php

namespace App\Services;

use App\Models\User;
use App\Models\SalaryBucket;

class SalaryService
{
    /**
     * توزيع الراتب بناءً على قاعدة 50/30/20
     */
    public function distributeSalary(User $user)
    {
        $salary = $user->total_salary;

        // حذف الأوعية القديمة في حال إعادة التوزيع (اختياري، يمكن تعديله حسب الحاجة)
        $user->salaryBuckets()->delete();

        // إنشاء وعاء الضروريات 50%
        $user->salaryBuckets()->create([
            'type' => 'needs',
            'limit_amount' => $salary * 0.50,
            'consumed_amount' => 0,
            'active_color' => 'green',
        ]);

        // إنشاء وعاء الرفاهية 30%
        $user->salaryBuckets()->create([
            'type' => 'wants',
            'limit_amount' => $salary * 0.30,
            'consumed_amount' => 0,
            'active_color' => 'green',
        ]);

        // إنشاء وعاء الادخار 20%
        $user->salaryBuckets()->create([
            'type' => 'savings',
            'limit_amount' => $salary * 0.20,
            'consumed_amount' => 0,
            'active_color' => 'blue', // اللون الأزرق يشير للادخار الإيجابي
        ]);
    }

    /**
     * تحديث حالة الوعاء واللون بعد إضافة مصروف
     */
    public function updateBucketStatus(SalaryBucket $bucket)
    {
        if ($bucket->limit_amount == 0) return;

        $percentage = ($bucket->consumed_amount / $bucket->limit_amount) * 100;

        if ($percentage < 80) {
            $bucket->active_color = 'green';
        } elseif ($percentage >= 80 && $percentage <= 100) {
            $bucket->active_color = 'orange';
        } else {
            $bucket->active_color = 'red';
        }

        // الادخار يبقى أزرق طالما لم يتم الصرف منه، إذا صُرف يمكن أن يتحول للون التحذير
        if ($bucket->type === 'savings' && $percentage == 0) {
            $bucket->active_color = 'blue';
        }

        $bucket->save();
    }
}
