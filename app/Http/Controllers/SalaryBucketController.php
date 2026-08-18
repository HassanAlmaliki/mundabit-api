<?php

namespace App\Http\Controllers;

use App\Services\SalaryService;
use Illuminate\Http\Request;

class SalaryBucketController extends Controller
{
    protected $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    /**
     * استرجاع جميع الأوعية المالية للمستخدم
     */
    public function index(Request $request)
    {
        $buckets = $request->user()->salaryBuckets;
        
        return response()->json([
            'data' => $buckets
        ]);
    }

    /**
     * توزيع الراتب وإعادة إنشاء الأوعية المالية 50/30/20
     */
    public function distribute(Request $request)
    {
        $user = $request->user();
        
        if ($user->total_salary <= 0) {
            return response()->json(['message' => 'يجب تعيين راتب أكبر من الصفر أولاً'], 400);
        }

        $this->salaryService->distributeSalary($user);

        return response()->json([
            'message' => 'تم توزيع الراتب بنجاح',
            'data' => $user->salaryBuckets
        ]);
    }

    /**
     * تحديث الراتب الكلي للمستخدم وإعادة التوزيع
     */
    public function updateSalary(Request $request)
    {
        $request->validate([
            'total_salary' => 'required|numeric|min:1',
        ]);

        $user = $request->user();
        $user->update(['total_salary' => $request->total_salary]);

        $this->salaryService->distributeSalary($user);

        return response()->json([
            'message' => 'تم تحديث الراتب وإعادة توزيع الأوعية المالية بنجاح',
            'data' => $user->salaryBuckets
        ]);
    }
}
