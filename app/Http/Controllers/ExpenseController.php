<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\SalaryBucket;
use App\Services\SalaryService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    protected $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    /**
     * استرجاع المصروفات لوعاء معين
     */
    public function index(Request $request, $bucket_id)
    {
        $bucket = $request->user()->salaryBuckets()->findOrFail($bucket_id);
        
        return response()->json([
            'data' => $bucket->expenses()->orderBy('date', 'desc')->get()
        ]);
    }

    /**
     * إضافة مصروف جديد لوعاء مالي
     */
    public function store(Request $request)
    {
        $request->validate([
            'bucket_id' => 'required|exists:salary_buckets,id',
            'amount' => 'required|numeric|min:0.1',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        $bucket = clone $request->user()->salaryBuckets()->findOrFail($request->bucket_id);

        if ($bucket->active_color === 'red') {
            return response()->json([
                'message' => 'لقد تجاوزت الميزانية في هذا الوعاء. يُمنع صرف المزيد (قفل الصرف)!'
            ], 403);
        }

        $expense = Expense::create([
            'user_id' => $request->user()->id,
            'bucket_id' => $bucket->id,
            'amount' => $request->amount,
            'category' => $request->category,
            'description' => $request->description,
            'date' => $request->date,
        ]);

        // تحديث الكمية المستهلكة في الوعاء
        $bucket->consumed_amount += $request->amount;
        
        // تحديث اللون وحالة الوعاء
        $this->salaryService->updateBucketStatus($bucket);

        return response()->json([
            'message' => 'تم إضافة المصروف وتحديث حالة الوعاء بنجاح.',
            'expense' => $expense,
            'bucket' => $bucket
        ], 201);
    }
}
