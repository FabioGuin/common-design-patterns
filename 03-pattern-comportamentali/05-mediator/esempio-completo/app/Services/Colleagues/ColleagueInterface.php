<?php

namespace App\Services\Colleagues;

interface ColleagueInterface
{
    public function setMediator(object $mediator): void;
    public function handleEvent(string $event, array $data = []): void;
}
