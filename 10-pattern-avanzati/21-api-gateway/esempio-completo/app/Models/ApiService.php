<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiService extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'version',
        'base_url',
        'health_endpoint',
        'status',
        'last_check',
        'registered_at',
        'config'
    ];

    protected $casts = [
        'config' => 'array',
        'last_check' => 'datetime',
        'registered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Verifica se il servizio è attivo
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Verifica se il servizio è inattivo
     */
    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    /**
     * Verifica se il servizio è sano
     */
    public function isHealthy()
    {
        return $this->status === 'healthy';
    }

    /**
     * Verifica se il servizio è malato
     */
    public function isUnhealthy()
    {
        return $this->status === 'unhealthy';
    }

    /**
     * Attiva il servizio
     */
    public function activate()
    {
        $this->status = 'active';
        $this->save();
    }

    /**
     * Disattiva il servizio
     */
    public function deactivate()
    {
        $this->status = 'inactive';
        $this->save();
    }

    /**
     * Marca il servizio come sano
     */
    public function markHealthy()
    {
        $this->status = 'healthy';
        $this->last_check = now();
        $this->save();
    }

    /**
     * Marca il servizio come malato
     */
    public function markUnhealthy()
    {
        $this->status = 'unhealthy';
        $this->last_check = now();
        $this->save();
    }

    /**
     * Ottiene l'URL completo del servizio
     */
    public function getFullUrl()
    {
        return rtrim($this->base_url, '/') . '/' . ltrim($this->health_endpoint, '/');
    }

    /**
     * Ottiene l'URL base del servizio
     */
    public function getBaseUrl()
    {
        return $this->base_url;
    }

    /**
     * Ottiene l'endpoint di health check
     */
    public function getHealthEndpoint()
    {
        return $this->health_endpoint;
    }

    /**
     * Ottiene la configurazione del servizio
     */
    public function getConfig()
    {
        return $this->config ?? [];
    }

    /**
     * Imposta la configurazione del servizio
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
        $this->save();
    }

    /**
     * Ottiene un valore di configurazione specifico
     */
    public function getConfigValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Imposta un valore di configurazione specifico
     */
    public function setConfigValue(string $key, $value)
    {
        $config = $this->config ?? [];
        $config[$key] = $value;
        $this->config = $config;
        $this->save();
    }

    /**
     * Ottiene il tempo dall'ultimo check
     */
    public function getTimeSinceLastCheck()
    {
        if (!$this->last_check) {
            return null;
        }

        return $this->last_check->diffForHumans();
    }

    /**
     * Ottiene il tempo dalla registrazione
     */
    public function getTimeSinceRegistration()
    {
        if (!$this->registered_at) {
            return null;
        }

        return $this->registered_at->diffForHumans();
    }

    /**
     * Verifica se il servizio è stato controllato di recente
     */
    public function wasCheckedRecently(int $minutes = 5)
    {
        if (!$this->last_check) {
            return false;
        }

        return $this->last_check->isAfter(now()->subMinutes($minutes));
    }

    /**
     * Ottiene le statistiche del servizio
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
            'healthy' => 'text-green-600',
            'unhealthy' => 'text-red-600',
            'active' => 'text-blue-600',
            'inactive' => 'text-gray-600'
        ];

        $color = $colors[$status] ?? 'text-gray-600';
        return "<span class='{$color}'>{$status}</span>";
    }

    /**
     * Ottiene la versione formattata
     */
    public function getFormattedVersion()
    {
        return "v{$this->version}";
    }

    /**
     * Ottiene l'URL formattato
     */
    public function getFormattedUrl()
    {
        $url = $this->getFullUrl();
        return "<a href='{$url}' target='_blank' class='text-blue-600 hover:text-blue-800'>{$url}</a>";
    }

    /**
     * Scope per filtrare per status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope per filtrare per versione
     */
    public function scopeWithVersion($query, $version)
    {
        return $query->where('version', $version);
    }

    /**
     * Scope per filtrare per servizi attivi
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'healthy']);
    }

    /**
     * Scope per filtrare per servizi inattivi
     */
    public function scopeInactive($query)
    {
        return $query->whereIn('status', ['inactive', 'unhealthy']);
    }

    /**
     * Scope per filtrare per servizi sani
     */
    public function scopeHealthy($query)
    {
        return $query->where('status', 'healthy');
    }

    /**
     * Scope per filtrare per servizi malati
     */
    public function scopeUnhealthy($query)
    {
        return $query->where('status', 'unhealthy');
    }

    /**
     * Scope per filtrare per servizi controllati di recente
     */
    public function scopeCheckedRecently($query, $minutes = 5)
    {
        return $query->where('last_check', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope per filtrare per servizi non controllati
     */
    public function scopeNotCheckedRecently($query, $minutes = 5)
    {
        return $query->where(function($q) use ($minutes) {
            $q->whereNull('last_check')
              ->orWhere('last_check', '<', now()->subMinutes($minutes));
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
            'version' => $this->version,
            'base_url' => $this->base_url,
            'health_endpoint' => $this->health_endpoint,
            'full_url' => $this->getFullUrl(),
            'status' => $this->status,
            'last_check' => $this->last_check?->toISOString(),
            'registered_at' => $this->registered_at?->toISOString(),
            'config' => $this->config,
            'is_active' => $this->isActive(),
            'is_inactive' => $this->isInactive(),
            'is_healthy' => $this->isHealthy(),
            'is_unhealthy' => $this->isUnhealthy(),
            'time_since_last_check' => $this->getTimeSinceLastCheck(),
            'time_since_registration' => $this->getTimeSinceRegistration(),
            'was_checked_recently' => $this->wasCheckedRecently(),
            'formatted_status' => $this->getFormattedStatus(),
            'formatted_version' => $this->getFormattedVersion(),
            'formatted_url' => $this->getFormattedUrl(),
            'stats' => $this->getStats(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString()
        ];
    }
}
