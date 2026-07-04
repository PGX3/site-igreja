<?php

namespace App\Channels;

use App\Models\DeviceToken;
use App\Notifications\Messages\FcmMessage;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FcmChannel
{
    public function send(mixed $notifiable, mixed $notification): void
    {
        if (! method_exists($notification, 'toFcm')) {
            return;
        }

        $tokens = $notifiable->routeNotificationFor('fcm', $notification);
        if (empty($tokens)) {
            return;
        }

        $message = $notification->toFcm($notifiable);
        if (! $message instanceof FcmMessage) {
            return;
        }

        $messaging = Firebase::messaging();
        $cloudMessage = CloudMessage::new()
            ->withNotification(FcmNotification::create($message->title, $message->body))
            ->withData($this->stringifyData($message->data));

        foreach ($tokens as $token) {
            try {
                $messaging->send($cloudMessage->toToken($token));
            } catch (NotFound) {
                DeviceToken::where('token', $token)->delete();
            } catch (\Throwable $e) {
                Log::warning("FCM send failed [token {$token}]: {$e->getMessage()}");
            }
        }
    }

    private function stringifyData(array $data): array
    {
        return array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v), $data);
    }
}
