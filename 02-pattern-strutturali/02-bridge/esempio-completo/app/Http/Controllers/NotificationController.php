<?php

namespace App\Http\Controllers;

use App\Services\MessageFormatterInterface;
use App\Services\HTMLFormatter;
use App\Services\TextFormatter;
use App\Services\JSONFormatter;
use App\Services\EmailNotification;
use App\Services\SMSNotification;
use App\Services\PushNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Mostra la pagina principale delle notifiche
     */
    public function index()
    {
        return view('notifications.index', [
            'availableChannels' => $this->getAvailableChannels(),
            'availableFormatters' => $this->getAvailableFormatters(),
        ]);
    }

    /**
     * Invia una notifica
     */
    public function sendNotification(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'channel' => 'required|string|in:email,sms,push',
            'formatter' => 'required|string|in:html,text,json',
            'title' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'device_token' => 'nullable|string',
            'action_url' => 'nullable|url',
            'action_text' => 'nullable|string|max:50',
        ]);

        try {
            $formatter = $this->createFormatter($request->formatter);
            $notification = $this->createNotification($request->channel, $formatter);

            $data = [
                'title' => $request->title,
                'email' => $request->email,
                'phone' => $request->phone,
                'device_token' => $request->device_token,
                'action_url' => $request->action_url,
                'action_text' => $request->action_text,
                'details' => [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()->toISOString(),
                ],
            ];

            $result = $notification->send($request->message, $data);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ottiene i canali disponibili
     */
    private function getAvailableChannels(): array
    {
        return [
            'email' => 'Email',
            'sms' => 'SMS',
            'push' => 'Push Notification',
        ];
    }

    /**
     * Ottiene i formattatori disponibili
     */
    private function getAvailableFormatters(): array
    {
        return [
            'html' => 'HTML',
            'text' => 'Text',
            'json' => 'JSON',
        ];
    }

    /**
     * Crea un formattatore
     */
    private function createFormatter(string $type): MessageFormatterInterface
    {
        return match ($type) {
            'html' => new HTMLFormatter(),
            'text' => new TextFormatter(),
            'json' => new JSONFormatter(),
            default => throw new \InvalidArgumentException("Unknown formatter: {$type}"),
        };
    }

    /**
     * Crea una notifica
     */
    private function createNotification(string $channel, MessageFormatterInterface $formatter)
    {
        return match ($channel) {
            'email' => new EmailNotification($formatter),
            'sms' => new SMSNotification($formatter),
            'push' => new PushNotification($formatter),
            default => throw new \InvalidArgumentException("Unknown channel: {$channel}"),
        };
    }
}
