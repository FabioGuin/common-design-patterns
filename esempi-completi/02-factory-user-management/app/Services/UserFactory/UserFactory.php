<?php

namespace App\Services\UserFactory;

use App\Models\User;
use App\Models\Role;

abstract class UserFactory implements UserFactoryInterface
{
    /**
     * Template method che definisce il processo di creazione
     */
    public function createUser(array $data): User
    {
        // Validazione dati base
        $this->validateUserData($data);
        
        // Creazione utente con caratteristiche specifiche
        $user = $this->createSpecificUser($data);
        
        // Assegnazione ruolo
        $this->assignRole($user);
        
        // Configurazione permessi
        $this->configurePermissions($user);
        
        // Salvataggio
        $user->save();
        
        return $user;
    }
    
    /**
     * Metodo astratto per la creazione specifica dell'utente
     */
    abstract protected function createSpecificUser(array $data): User;
    
    /**
     * Validazione dei dati utente
     */
    protected function validateUserData(array $data): void
    {
        if (empty($data['name']) || empty($data['email'])) {
            throw new \InvalidArgumentException('Name and email are required');
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
    }
    
    /**
     * Assegnazione del ruolo all'utente
     */
    protected function assignRole(User $user): void
    {
        $role = Role::where('name', $this->getUserType())->first();
        
        if (!$role) {
            $role = Role::create([
                'name' => $this->getUserType(),
                'description' => $this->getRoleDescription()
            ]);
        }
        
        $user->role()->associate($role);
    }
    
    /**
     * Configurazione dei permessi predefiniti
     */
    protected function configurePermissions(User $user): void
    {
        $permissions = $this->getDefaultPermissions();
        $user->permissions = json_encode($permissions);
    }
    
    /**
     * Restituisce la descrizione del ruolo
     */
    protected function getRoleDescription(): string
    {
        return match($this->getUserType()) {
            'admin' => 'Administrator with full access',
            'user' => 'Regular user with standard permissions',
            'guest' => 'Guest user with limited access',
            default => 'Unknown user type'
        };
    }
}
