<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
    }

    /**
     * تحليل السلوك المالي والإنجاز وتقديم توصيات مخصصة
     */
    public function analyzeBehavior(User $user)
    {
        // جمع بيانات الأوعية المالية
        $bucketsInfo = $user->salaryBuckets->map(function ($bucket) {
            return "وعاء {$bucket->type}: الميزانية ({$bucket->limit_amount})، المصروف ({$bucket->consumed_amount})، الحالة ({$bucket->active_color})";
        })->implode(' | ');

        // جمع بيانات الهدف الحالي
        $goalInfo = "لا يوجد هدف حالي.";
        if ($user->singleGoal) {
            $goal = $user->singleGoal;
            $goalInfo = "الهدف: {$goal->title}، مطلوب ({$goal->daily_hours_required}) ساعة/يوم، الإنجاز: {$goal->progress_percentage}%";
        }

        $prompt = "
        أنت مساعد ذكي ونظام صارم في تطبيق يسمى 'مُنضبِط'. هدفك مساعدة المستخدم على تحقيق انضباط مالي (قاعدة 50/30/20) وانضباط شخصي (الهدف الواحد).
        بيانات المستخدم الحالية:
        الراتب الإجمالي: {$user->total_salary}
        الموقف المالي: {$bucketsInfo}
        الإنجاز الشخصي: {$goalInfo}
        
        بناءً على هذه البيانات:
        1. قدم تحليلاً مختصراً (سطرين) لأسلوب صرفه الحالي وما إذا كان في خطر.
        2. قدم نصيحة مرنة حول كيفية تعويض التأخير في الهدف (إذا كان هناك تأخير).
        3. صغ رسالة دفع (Push Notification) تحفيزية ولكن بنبرة حازمة تعكس فلسفة 'مُنضبِط'.
        
        يرجى إرجاع الرد بصيغة JSON تحتوي على المفاتيح: analysis, advice, notification.
        ";

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(15)
                ->post($this->apiUrl, [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => 'أنت خبير في الانضباط المالي وتطوير الذات وتتبع لأسلوب صارم.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.7
                ]);

            if ($response->successful()) {
                return json_decode($response->json('choices.0.message.content'), true);
            }

            Log::error('AI Service Error: ' . $response->body());
            return $this->fallbackResponse();
            
        } catch (\Exception $e) {
            Log::error('AI Service Exception: ' . $e->getMessage());
            return $this->fallbackResponse();
        }
    }

    /**
     * الرد الافتراضي في حال فشل الاتصال بالذكاء الاصطناعي
     */
    private function fallbackResponse()
    {
        return [
            'analysis' => 'النظام غير قادر على تحليل بياناتك في الوقت الحالي. يرجى مراجعة ميزانيتك يدوياً.',
            'advice' => 'استمر في الالتزام بخطتك اليومية ولا تشتت نفسك بأهداف أخرى.',
            'notification' => 'انضباطك اليوم يحدد نجاحك غداً. استمر في التركيز!'
        ];
    }
}
