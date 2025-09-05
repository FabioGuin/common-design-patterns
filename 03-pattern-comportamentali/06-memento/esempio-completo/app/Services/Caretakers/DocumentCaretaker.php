<?php

namespace App\Services\Caretakers;

use App\Services\Mementos\DocumentMemento;

class DocumentCaretaker
{
    private array $mementos = [];
    private int $currentIndex = -1;
    
    public function saveMemento(DocumentMemento $memento): void
    {
        // Rimuovi tutti i mementi dopo l'indice corrente
        $this->mementos = array_slice($this->mementos, 0, $this->currentIndex + 1);
        
        // Aggiungi il nuovo memento
        $this->mementos[] = $memento;
        $this->currentIndex = count($this->mementos) - 1;
    }
    
    public function getMemento(int $index): ?DocumentMemento
    {
        return $this->mementos[$index] ?? null;
    }
    
    public function getCurrentMemento(): ?DocumentMemento
    {
        return $this->mementos[$this->currentIndex] ?? null;
    }
    
    public function undo(): ?DocumentMemento
    {
        if ($this->currentIndex > 0) {
            $this->currentIndex--;
            return $this->mementos[$this->currentIndex];
        }
        
        return null;
    }
    
    public function redo(): ?DocumentMemento
    {
        if ($this->currentIndex < count($this->mementos) - 1) {
            $this->currentIndex++;
            return $this->mementos[$this->currentIndex];
        }
        
        return null;
    }
    
    public function canUndo(): bool
    {
        return $this->currentIndex > 0;
    }
    
    public function canRedo(): bool
    {
        return $this->currentIndex < count($this->mementos) - 1;
    }
    
    public function getHistory(): array
    {
        return $this->mementos;
    }
    
    public function getCurrentIndex(): int
    {
        return $this->currentIndex;
    }
}
