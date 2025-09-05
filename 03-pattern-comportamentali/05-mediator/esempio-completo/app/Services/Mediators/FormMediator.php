<?php

namespace App\Services\Mediators;

class FormMediator implements MediatorInterface
{
    private array $colleagues = [];
    
    public function addColleague(object $colleague): void
    {
        $this->colleagues[] = $colleague;
        $colleague->setMediator($this);
    }
    
    public function notify(object $sender, string $event, array $data = []): void
    {
        foreach ($this->colleagues as $colleague) {
            if ($colleague !== $sender) {
                $colleague->handleEvent($event, $data);
            }
        }
    }
}
