<?php

namespace App\Services;

class JSONFormatter implements MessageFormatterInterface
{
    /**
     * Formatta un messaggio in JSON
     */
    public function format(string $message, array $data = []): string
    {
        $payload = [
            'notification' => [
                'title' => $data['title'] ?? 'Notifica',
                'message' => $message,
                'timestamp' => date('c'),
                'id' => uniqid('notif_', true),
            ],
            'data' => $data,
            'metadata' => [
                'formatter' => $this->getType(),
                'version' => '1.0',
                'generated_at' => microtime(true),
            ]
        ];

        if (!empty($data['action_url'])) {
            $payload['notification']['action'] = [
                'url' => $data['action_url'],
                'text' => $data['action_text'] ?? 'Visualizza',
            ];
        }

        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Ottiene il tipo di formattatore
     */
    public function getType(): string
    {
        return 'json';
    }

    /**
     * Verifica se il formattatore supporta un tipo di dato
     */
    public function supports(string $type): bool
    {
        return in_array($type, ['json', 'api', 'push', 'webhook']);
    }
}
