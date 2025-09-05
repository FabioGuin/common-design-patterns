<?php

namespace App\EventStore;

use App\Models\StoredEvent;
use Illuminate\Support\Facades\DB;

class EventStore
{
    public function saveEvents(string $aggregateId, array $events, int $expectedVersion): void
    {
        DB::transaction(function () use ($aggregateId, $events, $expectedVersion) {
            // Controllo di concorrenza
            $currentVersion = $this->getCurrentVersion($aggregateId);
            
            if ($currentVersion !== $expectedVersion) {
                throw new \ConcurrencyException(
                    "Expected version {$expectedVersion}, but current version is {$currentVersion}"
                );
            }

            // Salva eventi
            foreach ($events as $event) {
                $this->saveEvent($aggregateId, $event, $currentVersion + 1);
                $currentVersion++;
            }
        });
    }

    public function getEvents(string $aggregateId, int $fromVersion = 0): array
    {
        return StoredEvent::where('aggregate_id', $aggregateId)
            ->where('version', '>', $fromVersion)
            ->orderBy('version')
            ->get()
            ->map(fn($event) => $this->deserializeEvent($event))
            ->toArray();
    }

    public function getAllEvents(int $fromEventId = 0): array
    {
        return StoredEvent::where('id', '>', $fromEventId)
            ->orderBy('id')
            ->get()
            ->map(fn($event) => $this->deserializeEvent($event))
            ->toArray();
    }

    private function saveEvent(string $aggregateId, object $event, int $version): void
    {
        StoredEvent::create([
            'aggregate_id' => $aggregateId,
            'event_type' => get_class($event),
            'event_data' => json_encode($event),
            'version' => $version,
            'created_at' => now(),
        ]);
    }

    private function getCurrentVersion(string $aggregateId): int
    {
        return StoredEvent::where('aggregate_id', $aggregateId)
            ->max('version') ?? 0;
    }

    private function deserializeEvent(StoredEvent $storedEvent): object
    {
        $eventClass = $storedEvent->event_type;
        $eventData = json_decode($storedEvent->event_data, true);
        
        return new $eventClass(...$eventData);
    }
}
