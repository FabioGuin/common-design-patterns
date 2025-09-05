<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'method',
        'path',
        'url',
        'ip',
        'user_agent',
        'headers',
        'query',
        'body',
        'user_id',
        'status_code',
        'response_time',
        'success',
        'cached',
        'service',
        'gateway'
    ];

    protected $casts = [
        'headers' => 'array',
        'query' => 'array',
        'body' => 'array',
        'success' => 'boolean',
        'cached' => 'boolean',
        'response_time' => 'decimal:3',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Verifica se la richiesta è stata completata con successo
     */
    public function isSuccessful()
    {
        return $this->success;
    }

    /**
     * Verifica se la richiesta è fallita
     */
    public function isFailed()
    {
        return !$this->success;
    }

    /**
     * Verifica se la risposta è stata cachata
     */
    public function isCached()
    {
        return $this->cached;
    }

    /**
     * Ottiene il tempo di risposta in millisecondi
     */
    public function getResponseTimeMs()
    {
        return $this->response_time * 1000;
    }

    /**
     * Ottiene il tempo di risposta formattato
     */
    public function getFormattedResponseTime()
    {
        if ($this->response_time < 1) {
            return round($this->response_time * 1000, 2) . 'ms';
        }

        return round($this->response_time, 2) . 's';
    }

    /**
     * Ottiene lo status code formattato
     */
    public function getFormattedStatusCode()
    {
        $status = $this->status_code;
        
        if ($status >= 200 && $status < 300) {
            return "<span class='text-green-600'>{$status}</span>";
        } elseif ($status >= 300 && $status < 400) {
            return "<span class='text-blue-600'>{$status}</span>";
        } elseif ($status >= 400 && $status < 500) {
            return "<span class='text-yellow-600'>{$status}</span>";
        } elseif ($status >= 500) {
            return "<span class='text-red-600'>{$status}</span>";
        }

        return "<span class='text-gray-600'>{$status}</span>";
    }

    /**
     * Ottiene il metodo HTTP formattato
     */
    public function getFormattedMethod()
    {
        $method = strtoupper($this->method);
        
        $colors = [
            'GET' => 'text-blue-600',
            'POST' => 'text-green-600',
            'PUT' => 'text-yellow-600',
            'PATCH' => 'text-orange-600',
            'DELETE' => 'text-red-600'
        ];

        $color = $colors[$method] ?? 'text-gray-600';
        return "<span class='{$color}'>{$method}</span>";
    }

    /**
     * Ottiene il path formattato
     */
    public function getFormattedPath()
    {
        $path = $this->path;
        
        // Evidenzia i parametri
        $path = preg_replace('/\{[^}]+\}/', '<span class="text-purple-600">$0</span>', $path);
        
        return $path;
    }

    /**
     * Ottiene l'IP formattato
     */
    public function getFormattedIp()
    {
        $ip = $this->ip;
        
        // Maschera l'IP per privacy
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = '***';
            return implode('.', $parts);
        }
        
        return $ip;
    }

    /**
     * Ottiene l'user agent formattato
     */
    public function getFormattedUserAgent()
    {
        $userAgent = $this->user_agent;
        
        // Estrai il browser
        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            return 'Edge';
        }
        
        return 'Unknown';
    }

    /**
     * Ottiene le statistiche per questo endpoint
     */
    public function getEndpointStats()
    {
        $endpoint = $this->path;
        $method = $this->method;
        
        $stats = self::where('path', $endpoint)
            ->where('method', $method)
            ->selectRaw('
                COUNT(*) as total_requests,
                AVG(response_time) as avg_response_time,
                MIN(response_time) as min_response_time,
                MAX(response_time) as max_response_time,
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_requests,
                SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed_requests,
                SUM(CASE WHEN cached = 1 THEN 1 ELSE 0 END) as cached_requests
            ')
            ->first();
        
        return $stats;
    }

    /**
     * Ottiene le statistiche per questo utente
     */
    public function getUserStats()
    {
        if (!$this->user_id) {
            return null;
        }
        
        $stats = self::where('user_id', $this->user_id)
            ->selectRaw('
                COUNT(*) as total_requests,
                AVG(response_time) as avg_response_time,
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_requests,
                SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed_requests
            ')
            ->first();
        
        return $stats;
    }

    /**
     * Scope per filtrare per successo
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope per filtrare per fallimento
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope per filtrare per utente
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope per filtrare per endpoint
     */
    public function scopeForEndpoint($query, $path, $method = null)
    {
        $query = $query->where('path', $path);
        
        if ($method) {
            $query = $query->where('method', $method);
        }
        
        return $query;
    }

    /**
     * Scope per filtrare per periodo
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope per filtrare per tempo di risposta
     */
    public function scopeWithResponseTime($query, $minTime, $maxTime = null)
    {
        $query = $query->where('response_time', '>=', $minTime);
        
        if ($maxTime) {
            $query = $query->where('response_time', '<=', $maxTime);
        }
        
        return $query;
    }

    /**
     * Converte il modello in array per API
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'request_id' => $this->request_id,
            'method' => $this->method,
            'path' => $this->path,
            'url' => $this->url,
            'ip' => $this->ip,
            'user_agent' => $this->user_agent,
            'headers' => $this->headers,
            'query' => $this->query,
            'body' => $this->body,
            'user_id' => $this->user_id,
            'status_code' => $this->status_code,
            'response_time' => $this->response_time,
            'success' => $this->success,
            'cached' => $this->cached,
            'service' => $this->service,
            'gateway' => $this->gateway,
            'formatted_response_time' => $this->getFormattedResponseTime(),
            'formatted_status_code' => $this->getFormattedStatusCode(),
            'formatted_method' => $this->getFormattedMethod(),
            'formatted_path' => $this->getFormattedPath(),
            'formatted_ip' => $this->getFormattedIp(),
            'formatted_user_agent' => $this->getFormattedUserAgent(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString()
        ];
    }
}
