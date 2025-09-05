<?php

namespace App\Services\Mediators;

interface MediatorInterface
{
    public function notify(object $sender, string $event, array $data = []): void;
}
