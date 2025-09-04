<?php

namespace App\Services\UserFactory;

use App\Models\User;

interface UserFactoryInterface
{
    /**
     * Crea un nuovo utente con le caratteristiche specifiche del tipo
     */
    public function createUser(array $data): User;
    
    /**
     * Restituisce il tipo di utente gestito da questa factory
     */
    public function getUserType(): string;
    
    /**
     * Restituisce i permessi predefiniti per questo tipo di utente
     */
    public function getDefaultPermissions(): array;
}
