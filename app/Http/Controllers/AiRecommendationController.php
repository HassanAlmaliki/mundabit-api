<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use Illuminate\Http\Request;

class AiRecommendationController extends Controller
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * جلب توصيات وتحليلات الذكاء الاصطناعي للمستخدم
     */
    public function getRecommendations(Request $request)
    {
        $user = $request->user();

        // تأكد من تحميل العلاقات اللازمة
        $user->load('salaryBuckets', 'singleGoal');

        $aiFeedback = $this->aiService->analyzeBehavior($user);

        return response()->json([
            'message' => 'تم جلب تحليلات الذكاء الاصطناعي بنجاح.',
            'data' => $aiFeedback
        ]);
    }
}
