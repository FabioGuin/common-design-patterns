<?php

namespace App\Http\Controllers;

use App\Services\NotificationInterface;
use App\Services\BaseNotification;
use App\Services\LoggingDecorator;
use App\Services\CachingDecorator;
use App\Services\ValidationDecorator;
use App\Services\ThrottlingDecorator;
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
            'availableDecorators' => $this->getAvailableDecorators(),
        ]);
    }

    /**
     * Invia una notifica con decoratori
     */
    public function sendNotification(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'decorators' => 'required|array',
            'decorators.*' => 'string|in:logging,caching,validation,throttling',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'priority' => 'nullable|string|in:low,normal,high',
        ]);

        try {
            $notification = $this->createNotification($request->decorators);
            $data = $this->prepareData($request);

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
     * Ottiene le informazioni sui decoratori
     */
    public function getDecoratorInfo(Request $request): JsonResponse
    {
        $request->validate([
            'decorators' => 'required|array',
            'decorators.*' => 'string|in:logging,caching,validation,throttling',
        ]);

        try {
            $notification = $this->createNotification($request->decorators);
            
            $info = [
                'type' => $notification->getType(),
                'description' => $notification->getDescription(),
                'cost' => $notification->getCost(),
                'available' => $notification->isAvailable(),
            ];

            // Aggiungi informazioni specifiche per i decoratori
            if (in_array('throttling', $request->decorators)) {
                $throttlingDecorator = $this->findThrottlingDecorator($notification);
                if ($throttlingDecorator) {
                    $info['throttle_info'] = $throttlingDecorator->getThrottleInfo();
                }
            }

            return response()->json([
                'success' => true,
                'info' => $info,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resetta il throttling
     */
    public function resetThrottling(Request $request): JsonResponse
    {
        $request->validate([
            'decorators' => 'required|array',
            'decorators.*' => 'string|in:logging,caching,validation,throttling',
        ]);

        try {
            $notification = $this->createNotification($request->decorators);
            $throttlingDecorator = $this->findThrottlingDecorator($notification);

            if ($throttlingDecorator) {
                $throttlingDecorator->resetThrottling();
                return response()->json([
                    'success' => true,
                    'message' => 'Throttling reset successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Throttling decorator not found',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crea una notifica con i decoratori specificati
     */
    private function createNotification(array $decorators): NotificationInterface
    {
        $notification = new BaseNotification('email', 0.1, 'Email notification');

        foreach ($decorators as $decorator) {
            $notification = $this->applyDecorator($notification, $decorator);
        }

        return $notification;
    }

    /**
     * Applica un decoratore specifico
     */
    private function applyDecorator(NotificationInterface $notification, string $decorator): NotificationInterface
    {
        return match ($decorator) {
            'logging' => new LoggingDecorator($notification, 'info'),
            'caching' => new CachingDecorator($notification, config('notifications.cache_ttl', 300)),
            'validation' => new ValidationDecorator($notification, [
                'email' => 'required|email',
                'phone' => 'nullable|min:10',
                'priority' => 'nullable|in:low,normal,high',
            ]),
            'throttling' => new ThrottlingDecorator(
                $notification,
                config('notifications.throttle_limit', 5),
                config('notifications.throttle_window', 60)
            ),
            default => throw new \InvalidArgumentException("Unknown decorator: {$decorator}"),
        };
    }

    /**
     * Prepara i dati per la notifica
     */
    private function prepareData(Request $request): array
    {
        $data = [];

        if ($request->email) {
            $data['email'] = $request->email;
        }

        if ($request->phone) {
            $data['phone'] = $request->phone;
        }

        if ($request->priority) {
            $data['priority'] = $request->priority;
        }

        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->userAgent();
        $data['timestamp'] = now()->toISOString();

        return $data;
    }

    /**
     * Trova il decoratore di throttling nella catena
     */
    private function findThrottlingDecorator(NotificationInterface $notification): ?ThrottlingDecorator
    {
        if ($notification instanceof ThrottlingDecorator) {
            return $notification;
        }

        // In un'implementazione reale, dovresti implementare un metodo per navigare la catena
        // Per semplicità, assumiamo che il throttling sia sempre l'ultimo decoratore
        return null;
    }

    /**
     * Ottiene i decoratori disponibili
     */
    private function getAvailableDecorators(): array
    {
        return [
            'logging' => 'Logging',
            'caching' => 'Caching',
            'validation' => 'Validation',
            'throttling' => 'Throttling',
        ];
    }
}
