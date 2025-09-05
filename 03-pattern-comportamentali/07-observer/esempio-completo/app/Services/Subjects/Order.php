<?php

namespace App\Services\Subjects;

use App\Services\Observers\ObserverInterface;

class Order implements SubjectInterface
{
    private array $observers = [];
    private string $status = 'pending';
    private array $data = [];
    
    public function attach(ObserverInterface $observer): void
    {
        $this->observers[] = $observer;
    }
    
    public function detach(ObserverInterface $observer): void
    {
        $key = array_search($observer, $this->observers, true);
        if ($key !== false) {
            unset($this->observers[$key]);
        }
    }
    
    public function notify(): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
    
    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->notify();
    }
    
    public function getStatus(): string
    {
        return $this->status;
    }
    
    public function setData(array $data): void
    {
        $this->data = $data;
        $this->notify();
    }
    
    public function getData(): array
    {
        return $this->data;
    }
}
