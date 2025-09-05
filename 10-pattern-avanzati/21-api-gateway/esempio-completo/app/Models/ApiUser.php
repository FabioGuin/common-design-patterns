<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class ApiUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'api_key',
        'status',
        'last_login',
        'rate_limit',
        'rate_window'
    ];

    protected $hidden = [
        'password',
        'api_key'
    ];

    protected $casts = [
        'permissions' => 'array',
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Verifica se l'utente è attivo
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Verifica se l'utente è inattivo
     */
    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    /**
     * Verifica se l'utente è sospeso
     */
    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    /**
     * Attiva l'utente
     */
    public function activate()
    {
        $this->status = 'active';
        $this->save();
    }

    /**
     * Disattiva l'utente
     */
    public function deactivate()
    {
        $this->status = 'inactive';
        $this->save();
    }

    /**
     * Sospende l'utente
     */
    public function suspend()
    {
        $this->status = 'suspended';
        $this->save();
    }

    /**
     * Verifica se l'utente ha un ruolo specifico
     */
    public function hasRole(string $role)
    {
        return $this->role === $role;
    }

    /**
     * Verifica se l'utente ha uno dei ruoli specificati
     */
    public function hasAnyRole(array $roles)
    {
        return in_array($this->role, $roles);
    }

    /**
     * Verifica se l'utente ha un permesso specifico
     */
    public function hasPermission(string $permission)
    {
        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Verifica se l'utente ha uno dei permessi specificati
     */
    public function hasAnyPermission(array $permissions)
    {
        $userPermissions = $this->permissions ?? [];
        return !empty(array_intersect($permissions, $userPermissions));
    }

    /**
     * Aggiunge un permesso all'utente
     */
    public function addPermission(string $permission)
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }
    }

    /**
     * Rimuove un permesso dall'utente
     */
    public function removePermission(string $permission)
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        $this->permissions = array_values($permissions);
        $this->save();
    }

    /**
     * Ottiene tutti i permessi dell'utente
     */
    public function getAllPermissions()
    {
        $permissions = $this->permissions ?? [];
        
        // Aggiungi permessi basati su ruolo
        $rolePermissions = $this->getRolePermissions();
        $permissions = array_unique(array_merge($permissions, $rolePermissions));
        
        return $permissions;
    }

    /**
     * Ottiene i permessi per ruolo
     */
    private function getRolePermissions()
    {
        $rolePermissions = [
            'admin' => [
                'users.read', 'users.create', 'users.update', 'users.delete',
                'products.read', 'products.create', 'products.update', 'products.delete',
                'orders.read', 'orders.create', 'orders.update', 'orders.delete',
                'payments.read', 'payments.create', 'payments.update', 'payments.delete'
            ],
            'user' => [
                'users.read', 'users.update',
                'products.read',
                'orders.read', 'orders.create', 'orders.update',
                'payments.read', 'payments.create'
            ],
            'guest' => [
                'products.read'
            ]
        ];

        return $rolePermissions[$this->role] ?? [];
    }

    /**
     * Ottiene il livello di accesso dell'utente
     */
    public function getAccessLevel()
    {
        $accessLevels = [
            'admin' => 'full',
            'user' => 'limited',
            'guest' => 'readonly'
        ];

        return $accessLevels[$this->role] ?? 'none';
    }

    /**
     * Genera una nuova API key
     */
    public function generateApiKey()
    {
        $this->api_key = 'api_key_' . $this->id . '_' . uniqid();
        $this->save();
        
        return $this->api_key;
    }

    /**
     * Rigenera l'API key
     */
    public function regenerateApiKey()
    {
        $oldKey = $this->api_key;
        $newKey = $this->generateApiKey();
        
        return [
            'old_key' => $oldKey,
            'new_key' => $newKey
        ];
    }

    /**
     * Verifica se l'API key è valida
     */
    public function isApiKeyValid(string $apiKey)
    {
        return $this->api_key === $apiKey;
    }

    /**
     * Aggiorna l'ultimo login
     */
    public function updateLastLogin()
    {
        $this->last_login = now();
        $this->save();
    }

    /**
     * Ottiene il tempo dall'ultimo login
     */
    public function getTimeSinceLastLogin()
    {
        if (!$this->last_login) {
            return 'Never';
        }

        return $this->last_login->diffForHumans();
    }

    /**
     * Verifica se l'utente ha fatto login di recente
     */
    public function hasLoggedInRecently(int $minutes = 30)
    {
        if (!$this->last_login) {
            return false;
        }

        return $this->last_login->isAfter(now()->subMinutes($minutes));
    }

    /**
     * Ottiene il rate limit dell'utente
     */
    public function getRateLimit()
    {
        return $this->rate_limit ?? 100;
    }

    /**
     * Ottiene la finestra temporale del rate limit
     */
    public function getRateWindow()
    {
        return $this->rate_window ?? 60;
    }

    /**
     * Imposta il rate limit dell'utente
     */
    public function setRateLimit(int $limit, int $window = 60)
    {
        $this->rate_limit = $limit;
        $this->rate_window = $window;
        $this->save();
    }

    /**
     * Ottiene le statistiche dell'utente
     */
    public function getStats()
    {
        $stats = [
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'average_response_time' => 0,
            'last_request' => null
        ];

        // Simula statistiche
        $stats['total_requests'] = rand(100, 1000);
        $stats['successful_requests'] = rand(80, $stats['total_requests']);
        $stats['failed_requests'] = $stats['total_requests'] - $stats['successful_requests'];
        $stats['average_response_time'] = rand(100, 500) / 1000; // 100-500ms

        return $stats;
    }

    /**
     * Ottiene lo status formattato
     */
    public function getFormattedStatus()
    {
        $status = $this->status;
        
        $colors = [
            'active' => 'text-green-600',
            'inactive' => 'text-gray-600',
            'suspended' => 'text-red-600'
        ];

        $color = $colors[$status] ?? 'text-gray-600';
        return "<span class='{$color}'>{$status}</span>";
    }

    /**
     * Ottiene il ruolo formattato
     */
    public function getFormattedRole()
    {
        $role = $this->role;
        
        $colors = [
            'admin' => 'text-red-600',
            'user' => 'text-blue-600',
            'guest' => 'text-gray-600'
        ];

        $color = $colors[$role] ?? 'text-gray-600';
        return "<span class='{$color}'>{$role}</span>";
    }

    /**
     * Ottiene l'API key formattata
     */
    public function getFormattedApiKey()
    {
        if (!$this->api_key) {
            return 'Not generated';
        }

        $masked = substr($this->api_key, 0, 8) . '...' . substr($this->api_key, -4);
        return "<span class='font-mono text-sm'>{$masked}</span>";
    }

    /**
     * Scope per filtrare per status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope per filtrare per ruolo
     */
    public function scopeWithRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope per filtrare per utenti attivi
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope per filtrare per utenti inattivi
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope per filtrare per utenti sospesi
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    /**
     * Scope per filtrare per utenti con login recente
     */
    public function scopeLoggedInRecently($query, $minutes = 30)
    {
        return $query->where('last_login', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope per filtrare per utenti senza login recente
     */
    public function scopeNotLoggedInRecently($query, $minutes = 30)
    {
        return $query->where(function($q) use ($minutes) {
            $q->whereNull('last_login')
              ->orWhere('last_login', '<', now()->subMinutes($minutes));
        });
    }

    /**
     * Converte il modello in array per API
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'permissions' => $this->permissions,
            'status' => $this->status,
            'last_login' => $this->last_login?->toISOString(),
            'rate_limit' => $this->rate_limit,
            'rate_window' => $this->rate_window,
            'is_active' => $this->isActive(),
            'is_inactive' => $this->isInactive(),
            'is_suspended' => $this->isSuspended(),
            'access_level' => $this->getAccessLevel(),
            'all_permissions' => $this->getAllPermissions(),
            'time_since_last_login' => $this->getTimeSinceLastLogin(),
            'has_logged_in_recently' => $this->hasLoggedInRecently(),
            'formatted_status' => $this->getFormattedStatus(),
            'formatted_role' => $this->getFormattedRole(),
            'formatted_api_key' => $this->getFormattedApiKey(),
            'stats' => $this->getStats(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString()
        ];
    }
}
