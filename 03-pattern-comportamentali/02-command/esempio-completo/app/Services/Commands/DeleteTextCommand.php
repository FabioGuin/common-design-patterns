<?php

namespace App\Services\Commands;

use App\Services\Receivers\Document;

class DeleteTextCommand implements CommandInterface
{
    private Document $document;
    private int $position;
    private int $length;
    private string $deletedText;
    
    public function __construct(Document $document, int $position, int $length)
    {
        $this->document = $document;
        $this->position = $position;
        $this->length = $length;
        $this->deletedText = '';
    }
    
    public function execute(): void
    {
        // Salva il testo che verrà cancellato per l'undo
        $content = $this->document->getContent();
        $this->deletedText = substr($content, $this->position, $this->length);
        
        $this->document->deleteText($this->position, $this->length);
    }
    
    public function undo(): void
    {
        // Ripristina il testo cancellato
        $this->document->insertText($this->deletedText, $this->position);
    }
    
    public function getDescription(): string
    {
        return "Delete {$this->length} characters at position {$this->position}";
    }
}
