<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_type',
        'event_data',
        'aggregate_id',
        'version',
        'occurred_at'
    ];

    protected $casts = [
        'event_data' => 'array',
        'occurred_at' => 'datetime'
    ];

    /**
     * Scope per eventi di un tipo specifico
     */
    public function scopeByType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope per eventi di un aggregato specifico
     */
    public function scopeForAggregate($query, int $aggregateId)
    {
        return $query->where('aggregate_id', $aggregateId);
    }

    /**
     * Scope per eventi in un range di date
     */
    public function scopeInDateRange($query, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate)
    {
        return $query->whereBetween('occurred_at', [$startDate, $endDate]);
    }

    /**
     * Scope per eventi recenti
     */
    public function scopeRecent($query, int $limit = 100)
    {
        return $query->orderBy('occurred_at', 'desc')->limit($limit);
    }

    /**
     * Scope per eventi di un utente specifico
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->whereJsonContains('event_data->user_id', $userId);
    }

    /**
     * Scope per eventi con pattern di ricerca
     */
    public function scopeSearch($query, string $pattern)
    {
        return $query->where(function ($q) use ($pattern) {
            $q->where('event_type', 'LIKE', "%{$pattern}%")
              ->orWhere('event_data', 'LIKE', "%{$pattern}%");
        });
    }

    /**
     * Ottiene eventi per replay
     */
    public function scopeForReplay($query, \Carbon\Carbon $fromDate, ?\Carbon\Carbon $toDate = null)
    {
        $query->where('occurred_at', '>=', $fromDate);
        
        if ($toDate) {
            $query->where('occurred_at', '<=', $toDate);
        }
        
        return $query->orderBy('occurred_at');
    }

    /**
     * Ottiene eventi duplicati
     */
    public static function getDuplicates(): \Illuminate\Database\Eloquent\Collection
    {
        return static::select('event_id')
            ->groupBy('event_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
    }

    /**
     * Ottiene statistiche per tipo di evento
     */
    public static function getStatsByType(): array
    {
        return static::selectRaw('
                event_type,
                COUNT(*) as count,
                MIN(occurred_at) as first_occurrence,
                MAX(occurred_at) as last_occurrence
            ')
            ->groupBy('event_type')
            ->orderBy('count', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Ottiene statistiche per data
     */
    public static function getStatsByDate(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        return static::selectRaw('
                DATE(occurred_at) as date,
                COUNT(*) as count,
                COUNT(DISTINCT event_type) as unique_types
            ')
            ->where('occurred_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Ottiene eventi per un aggregato con versione specifica
     */
    public static function getForAggregateFromVersion(int $aggregateId, int $fromVersion): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('aggregate_id', $aggregateId)
            ->where('version', '>', $fromVersion)
            ->orderBy('version')
            ->get();
    }

    /**
     * Ottiene l'ultimo evento per un aggregato
     */
    public static function getLastForAggregate(int $aggregateId): ?self
    {
        return static::where('aggregate_id', $aggregateId)
            ->orderBy('version', 'desc')
            ->first();
    }

    /**
     * Ottiene la versione corrente di un aggregato
     */
    public static function getCurrentVersion(int $aggregateId): int
    {
        $lastEvent = static::getLastForAggregate($aggregateId);
        return $lastEvent ? $lastEvent->version : 0;
    }

    /**
     * Verifica se un evento esiste
     */
    public static function exists(string $eventId): bool
    {
        return static::where('event_id', $eventId)->exists();
    }

    /**
     * Ottiene eventi per un pattern di ricerca
     */
    public static function search(string $pattern): \Illuminate\Database\Eloquent\Collection
    {
        return static::where(function ($query) use ($pattern) {
            $query->where('event_type', 'LIKE', "%{$pattern}%")
                  ->orWhere('event_data', 'LIKE', "%{$pattern}%");
        })
        ->orderBy('occurred_at', 'desc')
        ->get();
    }

    /**
     * Ottiene eventi per un utente specifico
     */
    public static function getForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereJsonContains('event_data->user_id', $userId)
            ->orderBy('occurred_at', 'desc')
            ->get();
    }

    /**
     * Ottiene eventi per replay
     */
    public static function getForReplay(\Carbon\Carbon $fromDate, ?\Carbon\Carbon $toDate = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = static::where('occurred_at', '>=', $fromDate);
        
        if ($toDate) {
            $query->where('occurred_at', '<=', $toDate);
        }
        
        return $query->orderBy('occurred_at')->get();
    }

    /**
     * Pulisce eventi vecchi
     */
    public static function cleanupOld(int $daysOld = 365): int
    {
        $cutoffDate = now()->subDays($daysOld);
        
        return static::where('occurred_at', '<', $cutoffDate)->delete();
    }

    /**
     * Ottiene il totale degli eventi
     */
    public static function getTotalCount(): int
    {
        return static::count();
    }

    /**
     * Ottiene eventi per tipo
     */
    public static function getByType(string $eventType): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('event_type', $eventType)
            ->orderBy('occurred_at')
            ->get();
    }

    /**
     * Ottiene eventi per aggregato
     */
    public static function getForAggregate(int $aggregateId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('aggregate_id', $aggregateId)
            ->orderBy('version')
            ->get();
    }

    /**
     * Ottiene eventi in un range di date
     */
    public static function getInDateRange(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereBetween('occurred_at', [$startDate, $endDate])
            ->orderBy('occurred_at')
            ->get();
    }

    /**
     * Ottiene eventi recenti
     */
    public static function getRecent(int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return static::orderBy('occurred_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Ottiene eventi per un pattern di ricerca
     */
    public static function searchByPattern(string $pattern): \Illuminate\Database\Eloquent\Collection
    {
        return static::where(function ($query) use ($pattern) {
            $query->where('event_type', 'LIKE', "%{$pattern}%")
                  ->orWhere('event_data', 'LIKE', "%{$pattern}%");
        })
        ->orderBy('occurred_at', 'desc')
        ->get();
    }

    /**
     * Ottiene eventi per un utente specifico
     */
    public static function getByUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereJsonContains('event_data->user_id', $userId)
            ->orderBy('occurred_at', 'desc')
            ->get();
    }

    /**
     * Ottiene eventi per un aggregato con versione specifica
     */
    public static function getForAggregateToVersion(int $aggregateId, int $toVersion): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('aggregate_id', $aggregateId)
            ->where('version', '<=', $toVersion)
            ->orderBy('version')
            ->get();
    }

    /**
     * Ottiene eventi per un aggregato in un range di versioni
     */
    public static function getForAggregateInVersionRange(int $aggregateId, int $fromVersion, int $toVersion): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('aggregate_id', $aggregateId)
            ->where('version', '>', $fromVersion)
            ->where('version', '<=', $toVersion)
            ->orderBy('version')
            ->get();
    }
}
