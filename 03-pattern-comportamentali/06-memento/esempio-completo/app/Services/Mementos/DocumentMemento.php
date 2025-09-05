<?php

namespace App\Services\Mementos;

class DocumentMemento
{
    private string $content;
    private int $cursorPosition;
    private \DateTime $timestamp;
    
    public function __construct(string $content, int $cursorPosition)
    {
        $this->content = $content;
        $this->cursorPosition = $cursorPosition;
        $this->timestamp = new \DateTime();
    }
    
    public function getContent(): string
    {
        return $this->content;
    }
    
    public function getCursorPosition(): int
    {
        return $this->cursorPosition;
    }
    
    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }
}
