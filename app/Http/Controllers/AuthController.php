<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'total_salary' => 'required|numeric|min:0',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'total_salary' => $request->total_salary,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->fcm_token = null;
            $user->save();
        }

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح.'
        ]);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => ['required', 'string'],
        ]);

        $user = $request->user();
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json([
            'message' => 'FCM token updated successfully',
        ]);
    }

    public function sendTestNotification(Request $request, \App\Services\FcmService $fcmService)
    {
        $user = $request->user();
        if (!$user->fcm_token) {
            return response()->json(['message' => 'User does not have an FCM token.'], 400);
        }

        $title = $request->input('title', 'إشعار تجريبي من مُنضبِط 🔔');
        $body = $request->input('body', 'هذا إشعار تجريبي لاختبار نظام الإشعارات.');
        $data = [
            'type' => $request->input('type', 'general'),
            'screen' => $request->input('screen', 'notifications'),
            'id' => (string) $request->input('id', '1'),
        ];

        $result = $fcmService->sendToToken($user->fcm_token, $title, $body, $data);

        return response()->json([
            'message' => 'Notification triggered',
            'result' => $result,
        ]);
    }

    public function loginWithFirebase(Request $request)
    {
        $request->validate([
            'firebase_token' => 'required|string',
        ]);

        try {
            // Fetch Google's public keys (cached for 1 hour to improve performance)
            $keys = \Illuminate\Support\Facades\Cache::remember('firebase_public_keys', 3600, function () {
                return \Illuminate\Support\Facades\Http::get('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com')->json();
            });

            if (!$keys) {
                throw new \Exception("Could not fetch Firebase public keys.");
            }

            $keyObjects = [];
            foreach ($keys as $kid => $cert) {
                $publicKey = openssl_pkey_get_public($cert);
                if ($publicKey) {
                    $keyObjects[$kid] = new \Firebase\JWT\Key($publicKey, 'RS256');
                }
            }

            $decoded = \Firebase\JWT\JWT::decode($request->firebase_token, $keyObjects);

            // Verify Audience (Project ID)
            $projectId = 'mundabit-app';
            if ($decoded->aud !== $projectId) {
                throw new \Exception("Invalid audience.");
            }

            // Check expiration
            if (isset($decoded->exp) && time() > $decoded->exp) {
                throw new \Exception("Token is expired.");
            }

        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized - Invalid Token', 'error' => $e->getMessage()], 401);
        }

        $uid = $decoded->sub;
        $email = $decoded->email ?? null;
        $name = $decoded->name ?? 'User';

        if (!$email) {
            return response()->json(['message' => 'Unauthorized - No Email'], 401);
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(\Illuminate\Support\Str::random(24)),
                'total_salary' => 0,
            ]
        );

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }
}
