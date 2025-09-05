<?php

namespace App\Services;

use App\Events\ProductCreated;
use App\Events\ProductUpdated;
use App\Events\OrderCreated;
use App\Projections\ProductProjection;
use App\Projections\OrderProjection;

class EventBus
{
    private array $projections = [];

    public function __construct()
    {
        $this->projections = [
            ProductCreated::class => [new ProductProjection()],
            ProductUpdated::class => [new ProductProjection()],
            OrderCreated::class => [new OrderProjection()],
        ];
    }

    public function publish(object $event): void
    {
        $eventClass = get_class($event);
        
        if (!isset($this->projections[$eventClass])) {
            return;
        }

        foreach ($this->projections[$eventClass] as $projection) {
            try {
                $projection->handle($event);
            } catch (\Exception $e) {
                // Log error but don't stop the process
                \Log::error("Projection failed for event {$eventClass}: " . $e->getMessage());
            }
        }
    }

    public function subscribe(string $eventClass, callable $handler): void
    {
        if (!isset($this->projections[$eventClass])) {
            $this->projections[$eventClass] = [];
        }
        
        $this->projections[$eventClass][] = $handler;
    }
}
