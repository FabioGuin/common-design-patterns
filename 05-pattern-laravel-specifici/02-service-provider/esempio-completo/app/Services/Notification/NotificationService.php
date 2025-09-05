<?php

namespace App\Services\Notification;

use App\Services\Notification\Channels\EmailChannel;
use App\Services\Notification\Channels\SmsChannel;
use App\Services\Notification\Channels\PushChannel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;

class NotificationService
{
    protected array $channels;

    public function __construct(array $channels = [])
    {
        $this->channels = $channels;
    }

    /**
     * Invia notifica attraverso tutti i canali abilitati
     */
    public function send($notifiable, $notification, array $channels = null): array
    {
        $results = [];
        $channelsToUse = $channels ?? $this->getEnabledChannels();

        foreach ($channelsToUse as $channel) {
            try {
                $result = $this->sendViaChannel($notifiable, $notification, $channel);
                $results[$channel] = $result;

                // Log successo
                Log::info("Notification sent successfully via {$channel}", [
                    'notifiable' => get_class($notifiable),
                    'notification' => get_class($notification),
                    'channel' => $channel,
                ]);

                // Evento notifica inviata
                Event::dispatch(new \App\Events\NotificationSent($notifiable, $notification, $channel, $result));

            } catch (\Exception $e) {
                $results[$channel] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];

                // Log errore
                Log::error("Notification failed via {$channel}: " . $e->getMessage(), [
                    'notifiable' => get_class($notifiable),
                    'notification' => get_class($notification),
                    'channel' => $channel,
                    'error' => $e->getMessage(),
                ]);

                // Evento notifica fallita
                Event::dispatch(new \App\Events\NotificationFailed($notifiable, $notification, $channel, $e));
            }
        }

        return $results;
    }

    /**
     * Invia notifica via canale specifico
     */
    protected function sendViaChannel($notifiable, $notification, string $channel): array
    {
        $channelInstance = $this->getChannelInstance($channel);
        
        if (!$channelInstance) {
            throw new \Exception("Channel {$channel} not found");
        }

        if (!$channelInstance->isEnabled()) {
            throw new \Exception("Channel {$channel} is disabled");
        }

        return $channelInstance->send($notifiable, $notification);
    }

    /**
     * Ottieni istanza del canale
     */
    protected function getChannelInstance(string $channel)
    {
        switch ($channel) {
            case 'email':
                return app(EmailChannel::class);
            case 'sms':
                return app(SmsChannel::class);
            case 'push':
                return app(PushChannel::class);
            default:
                return null;
        }
    }

    /**
     * Ottieni canali abilitati
     */
    public function getEnabledChannels(): array
    {
        return collect($this->channels)
            ->filter(function ($channel) {
                return $channel->isEnabled();
            })
            ->keys()
            ->toArray();
    }

    /**
     * Ottieni tutti i canali
     */
    public function getAllChannels(): array
    {
        return array_keys($this->channels);
    }

    /**
     * Verifica se un canale è abilitato
     */
    public function isChannelEnabled(string $channel): bool
    {
        $channelInstance = $this->getChannelInstance($channel);
        return $channelInstance && $channelInstance->isEnabled();
    }

    /**
     * Abilita un canale
     */
    public function enableChannel(string $channel): bool
    {
        $channelInstance = $this->getChannelInstance($channel);
        if ($channelInstance) {
            return $channelInstance->enable();
        }
        return false;
    }

    /**
     * Disabilita un canale
     */
    public function disableChannel(string $channel): bool
    {
        $channelInstance = $this->getChannelInstance($channel);
        if ($channelInstance) {
            return $channelInstance->disable();
        }
        return false;
    }

    /**
     * Invia notifica bulk
     */
    public function sendBulk(array $notifiables, $notification, array $channels = null): array
    {
        $results = [];
        
        foreach ($notifiables as $notifiable) {
            $results[] = $this->send($notifiable, $notification, $channels);
        }

        return $results;
    }

    /**
     * Testa tutti i canali
     */
    public function testChannels(): array
    {
        $results = [];
        
        foreach ($this->channels as $name => $channel) {
            try {
                $results[$name] = [
                    'enabled' => $channel->isEnabled(),
                    'test' => $channel->test(),
                ];
            } catch (\Exception $e) {
                $results[$name] = [
                    'enabled' => $channel->isEnabled(),
                    'test' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Ottieni statistiche notifiche
     */
    public function getStats(): array
    {
        // Questo potrebbe essere implementato con un database per tracciare le notifiche
        return [
            'total_channels' => count($this->channels),
            'enabled_channels' => count($this->getEnabledChannels()),
            'channels' => $this->getChannelStats(),
        ];
    }

    /**
     * Ottieni statistiche per canale
     */
    protected function getChannelStats(): array
    {
        $stats = [];
        
        foreach ($this->channels as $name => $channel) {
            $stats[$name] = [
                'enabled' => $channel->isEnabled(),
                'priority' => $channel->getPriority(),
                'config' => $channel->getConfig(),
            ];
        }

        return $stats;
    }

    /**
     * Aggiungi canale personalizzato
     */
    public function addChannel(string $name, $channel): void
    {
        $this->channels[$name] = $channel;
    }

    /**
     * Rimuovi canale
     */
    public function removeChannel(string $name): bool
    {
        if (isset($this->channels[$name])) {
            unset($this->channels[$name]);
            return true;
        }
        return false;
    }
}
