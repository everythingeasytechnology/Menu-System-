<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushService
{
    public function isExpoPushToken(string $token): bool
    {
        return (bool) preg_match('/^Expo(nent)?PushToken\[.+\]$/', $token);
    }

    /**
     * Send a push notification through Expo's push API.
     *
     * Returns 'ok' on success, 'device_not_registered' when Expo reports the
     * token is permanently invalid (caller should stop using it), or 'error'
     * for any other failure.
     */
    public function send(string $token, string $title, string $body, array $data = []): string
    {
        if (! $this->isExpoPushToken($token)) {
            Log::warning('Skipped Expo push send for malformed token.', ['token' => $token]);

            return 'device_not_registered';
        }

        $accessToken = config('services.expo.access_token');

        try {
            $response = Http::withHeaders(array_filter([
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate',
                'Content-Type' => 'application/json',
                'Authorization' => $accessToken ? "Bearer {$accessToken}" : null,
            ]))->timeout(10)->post(config('services.expo.push_url'), [
                'to' => $token,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'sound' => 'default',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Expo push request failed to send.', [
                'token' => $token,
                'error' => $exception->getMessage(),
            ]);

            return 'error';
        }

        if ($response->failed()) {
            Log::error('Expo push API returned an error response.', [
                'token' => $token,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return 'error';
        }

        $ticket = $response->json('data');

        if (is_array($ticket) && array_is_list($ticket)) {
            $ticket = $ticket[0] ?? null;
        }

        $status = $ticket['status'] ?? null;

        if ($status === 'ok') {
            return 'ok';
        }

        $errorCode = $ticket['details']['error'] ?? null;

        Log::warning('Expo push ticket reported an error.', [
            'token' => $token,
            'ticket' => $ticket,
        ]);

        return $errorCode === 'DeviceNotRegistered' ? 'device_not_registered' : 'error';
    }
}
