<?php

namespace App\Services\Colleagues;

class Button implements ColleagueInterface
{
    private object $mediator;
    private string $text;
    private bool $enabled = true;
    
    public function __construct(string $text)
    {
        $this->text = $text;
    }
    
    public function setMediator(object $mediator): void
    {
        $this->mediator = $mediator;
    }
    
    public function handleEvent(string $event, array $data = []): void
    {
        if ($event === 'form_validation_failed') {
            $this->enabled = false;
            echo "Button '{$this->text}' disabled due to validation failure\n";
        } elseif ($event === 'form_validation_passed') {
            $this->enabled = true;
            echo "Button '{$this->text}' enabled\n";
        }
    }
    
    public function click(): void
    {
        if ($this->enabled) {
            $this->mediator->notify($this, 'button_clicked', ['button' => $this->text]);
        }
    }
    
    public function getText(): string
    {
        return $this->text;
    }
    
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
