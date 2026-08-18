<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\SalaryBucketController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GoalController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/firebase', [AuthController::class, 'loginWithFirebase']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Salary Buckets Endpoints
    Route::get('/buckets', [SalaryBucketController::class, 'index']);
    Route::post('/buckets/distribute', [SalaryBucketController::class, 'distribute']);
    Route::put('/buckets/salary', [SalaryBucketController::class, 'updateSalary']);

    // Expenses Endpoints
    Route::get('/buckets/{bucket_id}/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);

    // Goals Endpoints
    Route::get('/goals/current', [GoalController::class, 'current']);
    Route::post('/goals', [GoalController::class, 'store']);
    Route::post('/goals/progress', [GoalController::class, 'addProgress']);

    // AI Recommendations Endpoint
    Route::get('/ai/recommendations', [\App\Http\Controllers\AiRecommendationController::class, 'getRecommendations']);
});
