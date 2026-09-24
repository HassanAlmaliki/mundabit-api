<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected string $projectId;
    protected ?string $clientEmail;
    protected ?string $privateKey;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id', env('FIREBASE_PROJECT_ID', 'mundabit-app'));
        $this->clientEmail = config('services.firebase.client_email', env('FIREBASE_CLIENT_EMAIL'));
        
        $key = config('services.firebase.private_key', env('FIREBASE_PRIVATE_KEY'));
        if ($key) {
            $this->privateKey = str_replace('\n', "\n", $key);
        } else {
            $credentials = env('FIREBASE_CREDENTIALS');
            if ($credentials) {
                if (file_exists($credentials)) {
                    $json = json_decode(file_get_contents($credentials), true);
                } else {
                    $json = json_decode($credentials, true);
                }
                if ($json) {
                    $this->projectId = $json['project_id'] ?? $this->projectId;
                    $this->clientEmail = $json['client_email'] ?? $this->clientEmail;
                    $this->privateKey = $json['private_key'] ?? null;
                }
            } else {
                $this->privateKey = null;
            }
        }
    }

    /**
     * Get OAuth2 access token for FCM HTTP v1 API
     */
    protected function getAccessToken(): ?string
    {
        if (empty($this->clientEmail) || empty($this->privateKey)) {
            Log::warning('FCM credentials not configured in environment variables (FIREBASE_CLIENT_EMAIL, FIREBASE_PRIVATE_KEY, or FIREBASE_CREDENTIALS).');
            return null;
        }

        return Cache::remember('fcm_google_access_token', 3300, function () {
            $now = time();
            $payload = [
                'iss' => $this->clientEmail,
                'sub' => $this->clientEmail,
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            ];

            $jwt = JWT::encode($payload, $this->privateKey, 'RS256');

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('Failed to get FCM OAuth2 token: ' . $response->body());
            return null;
        });
    }

    /**
     * Send notification to a specific FCM token
     *
     * @param string $fcmToken
     * @param string|null $title
     * @param string|null $body
     * @param array $data Additional data payload (must be string => string)
     * @return array ['success' => bool, 'data' => mixed, 'error' => mixed]
     */
    public function sendToToken(string $fcmToken, ?string $title = null, ?string $body = null, array $data = []): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return [
                'success' => false,
                'error' => 'Missing or invalid Google FCM credentials in server environment variables.',
            ];
        }

        $formattedData = [];
        foreach ($data as $key => $value) {
            $formattedData[(string) $key] = is_null($value) ? '' : (string) $value;
        }

        $message = [
            'token' => $fcmToken,
            'data' => $formattedData,
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'mundabit_notifications',
                    'sound' => 'default',
                    'default_sound' => true,
                    'default_vibrate_timings' => true,
                ],
            ],
        ];

        if ($title !== null || $body !== null) {
            $message['notification'] = [
                'title' => $title ?? '',
                'body' => $body ?? '',
            ];
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $response = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, ['message' => $message]);

        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json(),
            ];
        }

        Log::error('FCM Send Error: ' . $response->body());
        return [
            'success' => false,
            'status' => $response->status(),
            'error' => $response->json() ?? $response->body(),
        ];
    }
}
