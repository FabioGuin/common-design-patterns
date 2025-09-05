<?php

namespace App\Services\Commands;

use App\Services\Receivers\Document;

class FormatTextCommand implements CommandInterface
{
    private Document $document;
    private int $position;
    private int $length;
    private string $format;
    private string $originalText;
    private string $formattedText;
    
    public function __construct(Document $document, int $position, int $length, string $format)
    {
        $this->document = $document;
        $this->position = $position;
        $this->length = $length;
        $this->format = $format;
        $this->originalText = '';
        $this->formattedText = '';
    }
    
    public function execute(): void
    {
        $content = $this->document->getContent();
        $this->originalText = substr($content, $this->position, $this->length);
        
        // Applica la formattazione
        $this->formattedText = $this->applyFormat($this->originalText, $this->format);
        
        // Sostituisci il testo formattato
        $newContent = substr_replace($content, $this->formattedText, $this->position, $this->length);
        $this->document->setContent($newContent);
    }
    
    public function undo(): void
    {
        // Ripristina il testo originale
        $content = $this->document->getContent();
        $newContent = substr_replace($content, $this->originalText, $this->position, strlen($this->formattedText));
        $this->document->setContent($newContent);
    }
    
    public function getDescription(): string
    {
        return "Format {$this->length} characters at position {$this->position} as {$this->format}";
    }
    
    private function applyFormat(string $text, string $format): string
    {
        return match ($format) {
            'bold' => "**{$text}**",
            'italic' => "*{$text}*",
            'underline' => "__{$text}__",
            'strikethrough' => "~~{$text}~~",
            'uppercase' => strtoupper($text),
            'lowercase' => strtolower($text),
            default => $text
        };
    }
}
