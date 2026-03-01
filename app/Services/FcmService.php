<?php

namespace App\Services;

use App\Models\FcmToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected ?array $credentials = null;
    protected ?string $projectId = null;
    protected ?string $accessToken = null;

    public function __construct()
    {
        $path = $this->resolveCredentialsPath();
        if (!file_exists($path)) {
            return;
        }
        $json = file_get_contents($path);
        $this->credentials = json_decode($json, true);
        if ($this->credentials) {
            $this->projectId = $this->credentials['project_id'] ?? null;
        }
    }

    public function isConfigured(): bool
    {
        return $this->credentials !== null && $this->projectId !== null;
    }

    public function getConfigError(): ?string
    {
        $path = $this->resolveCredentialsPath();
        if (!file_exists($path)) {
            return 'Firebase credentials file not found at: ' . $path;
        }
        $json = file_get_contents($path);
        $creds = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Firebase credentials file is invalid JSON.';
        }
        if (empty($creds['project_id']) || empty($creds['private_key']) || empty($creds['client_email'])) {
            return 'Firebase credentials must contain project_id, private_key, and client_email.';
        }
        return null;
    }

    protected function getAccessToken(): ?string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }
        if (!$this->credentials) {
            return null;
        }
        $jwt = $this->createJwt();
        if (!$jwt) {
            return null;
        }
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);
        if (!$response->successful()) {
            Log::warning('FCM OAuth2 token failed', ['body' => $response->body()]);
            return null;
        }
        $data = $response->json();
        $this->accessToken = $data['access_token'] ?? null;
        return $this->accessToken;
    }

    protected function createJwt(): ?string
    {
        $creds = $this->credentials;
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $now = time();
        $payload = [
            'iss' => $creds['client_email'],
            'sub' => $creds['client_email'],
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        ];
        $headerB64 = $this->base64UrlEncode(json_encode($header));
        $payloadB64 = $this->base64UrlEncode(json_encode($payload));
        $signatureInput = $headerB64 . '.' . $payloadB64;
        $key = openssl_pkey_get_private($creds['private_key']);
        if (!$key) {
            return null;
        }
        $signature = '';
        openssl_sign($signatureInput, $signature, $key, OPENSSL_ALGO_SHA256);
        $signatureB64 = $this->base64UrlEncode($signature);
        return $signatureInput . '.' . $signatureB64;
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Resolve credentials file path: use as-is if absolute, otherwise relative to base path.
     */
    protected function resolveCredentialsPath(): string
    {
        $path = config('services.firebase.credentials', 'storage/app/firebase-credentials.json');
        if ($path === null || $path === '') {
            $path = 'storage/app/firebase-credentials.json';
        }
        return str_starts_with($path, '/') ? $path : base_path($path);
    }

    /**
     * Return a human-readable reason why getAccessToken() failed.
     */
    protected function getTokenFailureReason(): string
    {
        $path = $this->resolveCredentialsPath();
        if (!file_exists($path)) {
            return 'Credentials file not found at: ' . $path . '. Upload firebase-credentials.json and set FIREBASE_CREDENTIALS in .env.';
        }
        $json = file_get_contents($path);
        $creds = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Credentials file is invalid JSON.';
        }
        if (empty($creds['project_id']) || empty($creds['private_key']) || empty($creds['client_email'])) {
            return 'Credentials file must contain project_id, private_key, and client_email (service account JSON from Firebase Console).';
        }
        $key = @openssl_pkey_get_private($creds['private_key']);
        if (!$key) {
            return 'Invalid private_key in credentials (openssl failed). Ensure the key is correct and use actual newlines if pasted.';
        }
        $this->credentials = $creds;
        $this->projectId = $creds['project_id'] ?? null;
        $jwt = $this->createJwt();
        if (!$jwt) {
            return 'Failed to create JWT from credentials.';
        }
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);
        if (!$response->successful()) {
            $body = $response->body();
            return 'Google OAuth2 rejected the request: ' . $response->status() . ' ' . (strlen($body) > 200 ? substr($body, 0, 200) . '...' : $body);
        }
        return 'Credentials and Google OAuth are OK but token was not used. Try "Send to all" again.';
    }

    /**
     * For debugging: path and whether file exists (no secrets).
     */
    public function getCredentialsPathDebug(): array
    {
        $path = $this->resolveCredentialsPath();
        return [
            'path' => $path,
            'exists' => file_exists($path),
        ];
    }

    /**
     * Send a notification to all registered FCM tokens.
     * Returns ['success' => int, 'failure' => int, 'errors' => array of strings].
     */
    public function sendToAll(string $title, string $body): array
    {
        $tokens = FcmToken::pluck('token')->unique()->values()->all();
        if (empty($tokens)) {
            return ['success' => 0, 'failure' => 0, 'errors' => ['No FCM tokens registered. Ask users to enable notifications from the app.']];
        }

        $token = $this->getAccessToken();
        if (!$token) {
            $reason = $this->getTokenFailureReason();
            return [
                'success' => 0,
                'failure' => count($tokens),
                'errors' => ['Could not get Firebase OAuth2 access token. ' . $reason],
            ];
        }

        $url = 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';
        $success = 0;
        $failure = 0;
        $errors = [];

        foreach ($tokens as $fcmToken) {
            $payload = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                ],
            ];
            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->successful()) {
                $success++;
            } else {
                $failure++;
                $errors[] = 'Token ' . substr($fcmToken, 0, 20) . '...: ' . $response->status() . ' ' . $response->body();
            }
        }

        return ['success' => $success, 'failure' => $failure, 'errors' => $errors];
    }
}
