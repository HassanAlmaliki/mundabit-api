<?php

namespace App\Http\Controllers;

use App\Models\SingleGoal;
use App\Models\GoalProgress;
use App\Services\GoalService;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    protected $goalService;

    public function __construct(GoalService $goalService)
    {
        $this->goalService = $goalService;
    }

    /**
     * عرض الهدف الحالي للمستخدم (الهدف الواحد)
     */
    public function current(Request $request)
    {
        $goal = $request->user()->singleGoal;
        return response()->json(['data' => $goal]);
    }

    /**
     * إنشاء هدف جديد
     * يمنع التطبيق وجود أكثر من هدف غير مكتمل
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $currentGoal = $user->singleGoal;

        if ($currentGoal && $currentGoal->lock_status) {
            return response()->json([
                'message' => 'لا يمكنك إضافة هدف جديد حتى تصل نسبة إنجاز الهدف الحالي إلى 90% على الأقل أو تلغيه.'
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'total_hours' => 'required|integer|min:1',
            'total_days' => 'required|integer|min:1',
        ]);

        // إذا كان هناك هدف قديم مكتمل، يمكن حذفه أو أرشفته (هنا سنحذفه لتبسيط قاعدة "الهدف الواحد")
        if ($currentGoal) {
            $currentGoal->delete();
        }

        $goal = SingleGoal::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'total_hours' => $request->total_hours,
            'total_days' => $request->total_days,
            'daily_hours_required' => 0,
            'progress_percentage' => 0,
            'lock_status' => true,
        ]);

        $this->goalService->calculateDailyHours($goal);

        return response()->json([
            'message' => 'تم تحديد هدفك الجديد بنجاح!',
            'data' => $goal
        ], 201);
    }

    /**
     * تسجيل إنجاز يومي وتحديث التقدم
     */
    public function addProgress(Request $request)
    {
        $request->validate([
            'completed_hours' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $goal = $request->user()->singleGoal;

        if (!$goal) {
            return response()->json(['message' => 'لا يوجد هدف حالي لتسجيل الإنجاز فيه.'], 404);
        }

        $progress = GoalProgress::create([
            'goal_id' => $goal->id,
            'completed_hours' => $request->completed_hours,
            'notes' => $request->notes,
            'date' => $request->date,
        ]);

        $this->goalService->updateProgress($goal);

        return response()->json([
            'message' => 'تم تسجيل الإنجاز اليومي. استمر في التركيز!',
            'progress' => $progress,
            'goal' => $goal
        ], 201);
    }
}
