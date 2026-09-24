<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FcmService;
use Illuminate\Console\Command;

class SendFcmNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:send 
                            {target : User ID, User Email, or FCM Token}
                            {--title=إشعار جديد من مُنضبِط 🔔 : Notification Title}
                            {--body=هذا إشعار تجريبي : Notification Body}
                            {--type=general : Payload type (e.g. order, expense, goal, salary, general)}
                            {--screen=notifications : Target screen (e.g. order_details, notifications, expense_details)}
                            {--id= : Target ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a Firebase Cloud Messaging (FCM) notification to a user or token';

    /**
     * Execute the console command.
     */
    public function handle(FcmService $fcmService)
    {
        $target = $this->argument('target');
        $fcmToken = null;

        // Check if target is user ID or email
        if (is_numeric($target)) {
            $user = User::find($target);
            $fcmToken = $user?->fcm_token;
        } elseif (filter_var($target, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $target)->first();
            $fcmToken = $user?->fcm_token;
        } else {
            $fcmToken = $target;
        }

        if (empty($fcmToken)) {
            $this->error("No FCM token found for target: {$target}");
            return Command::FAILURE;
        }

        $title = $this->option('title');
        $body = $this->option('body');
        $data = [
            'type' => $this->option('type') ?? 'general',
            'screen' => $this->option('screen') ?? 'notifications',
        ];

        if ($this->option('id')) {
            $data['id'] = (string) $this->option('id');
        }

        $this->info("Sending FCM to token: " . substr($fcmToken, 0, 20) . "...");

        $result = $fcmService->sendToToken($fcmToken, $title, $body, $data);

        if ($result['success']) {
            $this->info("Notification sent successfully!");
            $this->line(json_encode($result['data'], JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        } else {
            $this->error("Failed to send notification: " . json_encode($result['error']));
            return Command::FAILURE;
        }
    }
}
