<?php

namespace App\Services\Commands;

class MacroCommand implements CommandInterface
{
    private array $commands = [];
    
    public function addCommand(CommandInterface $command): void
    {
        $this->commands[] = $command;
    }
    
    public function execute(): void
    {
        foreach ($this->commands as $command) {
            $command->execute();
        }
    }
    
    public function undo(): void
    {
        // Annulla i comandi in ordine inverso
        $reversedCommands = array_reverse($this->commands);
        foreach ($reversedCommands as $command) {
            $command->undo();
        }
    }
    
    public function getDescription(): string
    {
        $commandDescriptions = array_map(function($command) {
            return $command->getDescription();
        }, $this->commands);
        
        return "Macro: " . implode('; ', $commandDescriptions);
    }
    
    public function getCommands(): array
    {
        return $this->commands;
    }
    
    public function getCommandCount(): int
    {
        return count($this->commands);
    }
}
