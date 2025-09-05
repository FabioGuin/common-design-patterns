<?php

namespace App\Services\Commands;

use App\Services\Receivers\Document;

class WriteTextCommand implements CommandInterface
{
    private Document $document;
    private string $text;
    private int $position;
    
    public function __construct(Document $document, string $text, int $position)
    {
        $this->document = $document;
        $this->text = $text;
        $this->position = $position;
    }
    
    public function execute(): void
    {
        $this->document->insertText($this->text, $this->position);
    }
    
    public function undo(): void
    {
        $this->document->deleteText($this->position, strlen($this->text));
    }
    
    public function getDescription(): string
    {
        return "Write '{$this->text}' at position {$this->position}";
    }
}
