<?php

namespace App\Channels;

use App\Models\DeviceToken;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notifiable->push_enabled) {
            return;
        }

        if (! method_exists($notification, 'toExpoPush')) {
            return;
        }

        $message = $notification->toExpoPush($notifiable);
        $tokens = $notifiable->deviceTokens()->pluck('token')->all();

        if ($tokens === []) {
            return;
        }

        $payload = array_map(fn (string $token) => [
            'to' => $token,
            'title' => $message['title'] ?? 'GMAO+',
            'body' => $message['body'] ?? '',
            'data' => $message['data'] ?? [],
            'sound' => 'default',
        ], $tokens);

        try {
            $response = Http::acceptJson()
                ->post('https://exp.host/--/api/v2/push/send', $payload);

            if (! $response->successful()) {
                Log::warning('Expo push failed', ['status' => $response->status(), 'body' => $response->body()]);

                return;
            }

            $this->purgeInvalidTokens($response->json('data', []));
        } catch (\Throwable $e) {
            Log::warning('Expo push error', ['message' => $e->getMessage()]);
        }
    }

    /** @param array<int, array<string, mixed>> $results */
    protected function purgeInvalidTokens(array $results): void
    {
        foreach ($results as $result) {
            $status = $result['status'] ?? null;
            $details = $result['details'] ?? [];
            $error = $details['error'] ?? null;

            if ($status === 'error' && in_array($error, ['DeviceNotRegistered', 'InvalidCredentials'], true)) {
                $token = $result['to'] ?? $details['expoPushToken'] ?? null;
                if ($token) {
                    DeviceToken::where('token', $token)->delete();
                }
            }
        }
    }
}
