<?php

namespace App\Services\Invokers;

use App\Services\Commands\CommandInterface;

class CommandInvoker
{
    private array $history = [];
    private int $currentPosition = -1;
    
    public function executeCommand(CommandInterface $command): void
    {
        $command->execute();
        
        // Rimuovi comandi futuri se siamo nel mezzo della history
        $this->history = array_slice($this->history, 0, $this->currentPosition + 1);
        
        // Aggiungi il nuovo comando
        $this->history[] = $command;
        $this->currentPosition++;
    }
    
    public function undo(): bool
    {
        if ($this->currentPosition >= 0) {
            $this->history[$this->currentPosition]->undo();
            $this->currentPosition--;
            return true;
        }
        return false;
    }
    
    public function redo(): bool
    {
        if ($this->currentPosition < count($this->history) - 1) {
            $this->currentPosition++;
            $this->history[$this->currentPosition]->execute();
            return true;
        }
        return false;
    }
    
    public function canUndo(): bool
    {
        return $this->currentPosition >= 0;
    }
    
    public function canRedo(): bool
    {
        return $this->currentPosition < count($this->history) - 1;
    }
    
    public function getHistory(): array
    {
        return $this->history;
    }
}
