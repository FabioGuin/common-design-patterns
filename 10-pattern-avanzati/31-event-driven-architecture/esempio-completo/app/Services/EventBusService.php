<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

class EventBusService
{
    /**
     * Pubblica un evento nell'event bus
     */
    public function publish(string $eventClass, array $eventData): void
    {
        try {
            Log::info('Publishing event', [
                'event_class' => $eventClass,
                'event_data' => $eventData
            ]);

            // Crea l'istanza dell'evento
            $event = new $eventClass($eventData);

            // Pubblica l'evento
            Event::dispatch($event);

            Log::info('Event published successfully', [
                'event_class' => $eventClass
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to publish event', [
                'event_class' => $eventClass,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Pubblica un evento in modo asincrono
     */
    public function publishAsync(string $eventClass, array $eventData): void
    {
        try {
            Log::info('Publishing event asynchronously', [
                'event_class' => $eventClass,
                'event_data' => $eventData
            ]);

            // Crea l'istanza dell'evento
            $event = new $eventClass($eventData);

            // Pubblica l'evento in modo asincrono
            Event::dispatch($event);

            Log::info('Event published asynchronously successfully', [
                'event_class' => $eventClass
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to publish event asynchronously', [
                'event_class' => $eventClass,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Pubblica un batch di eventi
     */
    public function publishBatch(array $events): void
    {
        foreach ($events as $event) {
            $this->publish($event['class'], $event['data']);
        }
    }

    /**
     * Sottoscrive a un evento
     */
    public function subscribe(string $eventClass, string $listenerClass): void
    {
        Event::listen($eventClass, $listenerClass);
        
        Log::info('Event subscription created', [
            'event_class' => $eventClass,
            'listener_class' => $listenerClass
        ]);
    }

    /**
     * Sottoscrive a un evento con priorità
     */
    public function subscribeWithPriority(string $eventClass, string $listenerClass, int $priority = 0): void
    {
        Event::listen($eventClass, $listenerClass, $priority);
        
        Log::info('Event subscription created with priority', [
            'event_class' => $eventClass,
            'listener_class' => $listenerClass,
            'priority' => $priority
        ]);
    }

    /**
     * Rimuove una sottoscrizione
     */
    public function unsubscribe(string $eventClass, string $listenerClass): void
    {
        Event::forget($eventClass, $listenerClass);
        
        Log::info('Event subscription removed', [
            'event_class' => $eventClass,
            'listener_class' => $listenerClass
        ]);
    }

    /**
     * Ottiene tutti i listener per un evento
     */
    public function getListeners(string $eventClass): array
    {
        return Event::getListeners($eventClass);
    }

    /**
     * Ottiene tutte le sottoscrizioni
     */
    public function getSubscriptions(): array
    {
        $subscriptions = [];
        
        // In Laravel, non c'è un modo diretto per ottenere tutte le sottoscrizioni
        // Questo è un esempio semplificato
        $events = [
            'App\Events\OrderCreated',
            'App\Events\OrderUpdated',
            'App\Events\OrderCancelled',
            'App\Events\PaymentProcessed',
            'App\Events\InventoryUpdated'
        ];

        foreach ($events as $eventClass) {
            $subscriptions[$eventClass] = $this->getListeners($eventClass);
        }

        return $subscriptions;
    }

    /**
     * Verifica se un evento ha listener
     */
    public function hasListeners(string $eventClass): bool
    {
        return !empty($this->getListeners($eventClass));
    }

    /**
     * Pulisce tutte le sottoscrizioni
     */
    public function clearSubscriptions(): void
    {
        Event::flush();
        
        Log::info('All event subscriptions cleared');
    }

    /**
     * Pubblica un evento con retry
     */
    public function publishWithRetry(string $eventClass, array $eventData, int $maxRetries = 3): void
    {
        $attempts = 0;
        
        while ($attempts < $maxRetries) {
            try {
                $this->publish($eventClass, $eventData);
                return; // Successo, esci dal loop
            } catch (\Exception $e) {
                $attempts++;
                
                if ($attempts >= $maxRetries) {
                    Log::error('Event publishing failed after all retries', [
                        'event_class' => $eventClass,
                        'attempts' => $attempts,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
                
                Log::warning('Event publishing failed, retrying', [
                    'event_class' => $eventClass,
                    'attempt' => $attempts,
                    'max_retries' => $maxRetries,
                    'error' => $e->getMessage()
                ]);
                
                // Attendi prima di riprovare (backoff esponenziale)
                sleep(pow(2, $attempts));
            }
        }
    }

    /**
     * Pubblica un evento con delay
     */
    public function publishWithDelay(string $eventClass, array $eventData, int $delaySeconds = 0): void
    {
        if ($delaySeconds > 0) {
            // In un sistema reale, useresti un job con delay
            Log::info('Event scheduled for later publication', [
                'event_class' => $eventClass,
                'delay_seconds' => $delaySeconds
            ]);
            
            // Per semplicità, pubblichiamo subito
            $this->publish($eventClass, $eventData);
        } else {
            $this->publish($eventClass, $eventData);
        }
    }

    /**
     * Ottiene statistiche dell'event bus
     */
    public function getStats(): array
    {
        $subscriptions = $this->getSubscriptions();
        
        return [
            'total_events' => count($subscriptions),
            'total_listeners' => array_sum(array_map('count', $subscriptions)),
            'events_with_listeners' => count(array_filter($subscriptions, function($listeners) {
                return !empty($listeners);
            })),
            'events_without_listeners' => count(array_filter($subscriptions, function($listeners) {
                return empty($listeners);
            }))
        ];
    }

    /**
     * Testa la connessione all'event bus
     */
    public function testConnection(): bool
    {
        try {
            // Testa pubblicando un evento di test
            $this->publish('App\Events\TestEvent', ['test' => true]);
            return true;
        } catch (\Exception $e) {
            Log::error('Event bus connection test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
