<?php

namespace App\Services\Receivers;

class Document
{
    private string $content = '';
    private int $cursorPosition = 0;
    
    public function insertText(string $text, int $position): void
    {
        $this->content = substr_replace($this->content, $text, $position, 0);
        $this->cursorPosition = $position + strlen($text);
    }
    
    public function deleteText(int $start, int $length): void
    {
        $this->content = substr_replace($this->content, '', $start, $length);
        $this->cursorPosition = $start;
    }
    
    public function getContent(): string
    {
        return $this->content;
    }
    
    public function getCursorPosition(): int
    {
        return $this->cursorPosition;
    }
    
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
