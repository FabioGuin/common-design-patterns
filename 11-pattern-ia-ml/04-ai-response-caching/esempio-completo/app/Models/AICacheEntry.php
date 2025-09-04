<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AICacheEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_key',
        'cache_key',
        'strategy',
        'ttl',
        'expires_at',
        'size',
        'compressed',
        'tags',
        'hit_count',
        'last_accessed_at',
        'metadata'
    ];

    protected $casts = [
        'tags' => 'array',
        'metadata' => 'array',
        'compressed' => 'boolean',
        'expires_at' => 'datetime',
        'last_accessed_at' => 'datetime'
    ];

    /**
     * Scope per chiavi scadute
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope per strategia specifica
     */
    public function scopeByStrategy($query, string $strategy)
    {
        return $query->where('strategy', $strategy);
    }

    /**
     * Scope per tag specifico
     */
    public function scopeByTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Scope per chiavi che scadranno presto
     */
    public function scopeExpiringSoon($query, int $minutes = 5)
    {
        return $query->where('expires_at', '<=', now()->addMinutes($minutes));
    }

    /**
     * Scope per chiavi più utilizzate
     */
    public function scopeMostUsed($query, int $limit = 10)
    {
        return $query->orderBy('hit_count', 'desc')->limit($limit);
    }

    /**
     * Scope per chiavi meno utilizzate
     */
    public function scopeLeastUsed($query, int $limit = 10)
    {
        return $query->orderBy('hit_count', 'asc')->limit($limit);
    }

    /**
     * Scope per chiavi più grandi
     */
    public function scopeLargest($query, int $limit = 10)
    {
        return $query->orderBy('size', 'desc')->limit($limit);
    }

    /**
     * Scope per chiavi più piccole
     */
    public function scopeSmallest($query, int $limit = 10)
    {
        return $query->orderBy('size', 'asc')->limit($limit);
    }

    /**
     * Scope per chiavi compresse
     */
    public function scopeCompressed($query)
    {
        return $query->where('compressed', true);
    }

    /**
     * Scope per chiavi non compresse
     */
    public function scopeUncompressed($query)
    {
        return $query->where('compressed', false);
    }

    /**
     * Scope per pattern di chiave
     */
    public function scopeByPattern($query, string $pattern)
    {
        return $query->where('original_key', 'like', str_replace('*', '%', $pattern));
    }

    /**
     * Scope per dimensione
     */
    public function scopeBySize($query, int $minSize, int $maxSize = null)
    {
        if ($maxSize) {
            return $query->whereBetween('size', [$minSize, $maxSize]);
        }
        return $query->where('size', '>=', $minSize);
    }

    /**
     * Scope per hit count
     */
    public function scopeByHitCount($query, int $minHits, int $maxHits = null)
    {
        if ($maxHits) {
            return $query->whereBetween('hit_count', [$minHits, $maxHits]);
        }
        return $query->where('hit_count', '>=', $minHits);
    }

    /**
     * Scope per data di creazione
     */
    public function scopeByCreatedAt($query, $startDate, $endDate = null)
    {
        if ($endDate) {
            return $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        return $query->where('created_at', '>=', $startDate);
    }

    /**
     * Scope per ultimo accesso
     */
    public function scopeByLastAccessed($query, $startDate, $endDate = null)
    {
        if ($endDate) {
            return $query->whereBetween('last_accessed_at', [$startDate, $endDate]);
        }
        return $query->where('last_accessed_at', '>=', $startDate);
    }

    /**
     * Verifica se la chiave è scaduta
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * Verifica se la chiave scadrà presto
     */
    public function isExpiringSoon(int $minutes = 5): bool
    {
        return $this->expires_at <= now()->addMinutes($minutes);
    }

    /**
     * Ottiene il tempo rimanente in secondi
     */
    public function getTimeToLive(): int
    {
        return max(0, $this->expires_at->diffInSeconds(now()));
    }

    /**
     * Ottiene la dimensione formattata
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Ottiene i tag come stringa
     */
    public function getTagsStringAttribute(): string
    {
        return implode(', ', $this->tags ?? []);
    }

    /**
     * Ottiene le statistiche di utilizzo
     */
    public function getUsageStatsAttribute(): array
    {
        return [
            'hit_count' => $this->hit_count,
            'last_accessed' => $this->last_accessed_at,
            'time_since_last_access' => $this->last_accessed_at ? $this->last_accessed_at->diffForHumans() : 'Never',
            'is_active' => !$this->isExpired()
        ];
    }

    /**
     * Ottiene le statistiche di performance
     */
    public function getPerformanceStatsAttribute(): array
    {
        return [
            'size' => $this->size,
            'formatted_size' => $this->formatted_size,
            'compressed' => $this->compressed,
            'compression_ratio' => $this->compressed ? $this->calculateCompressionRatio() : 1.0,
            'efficiency' => $this->calculateEfficiency()
        ];
    }

    /**
     * Calcola il rapporto di compressione
     */
    private function calculateCompressionRatio(): float
    {
        // Implementazione semplificata - in produzione calcolare il rapporto reale
        return $this->compressed ? 0.5 : 1.0;
    }

    /**
     * Calcola l'efficienza della chiave
     */
    private function calculateEfficiency(): float
    {
        $age = $this->created_at->diffInDays(now());
        $hits = $this->hit_count;
        
        if ($age == 0) {
            return $hits > 0 ? 1.0 : 0.0;
        }
        
        return min(1.0, $hits / $age);
    }

    /**
     * Aggiorna le statistiche di accesso
     */
    public function recordAccess(): void
    {
        $this->increment('hit_count');
        $this->update(['last_accessed_at' => now()]);
    }

    /**
     * Ottiene le statistiche aggregate per strategia
     */
    public static function getStatsByStrategy(): array
    {
        return static::select('strategy')
            ->selectRaw('COUNT(*) as total_entries')
            ->selectRaw('SUM(size) as total_size')
            ->selectRaw('AVG(size) as avg_size')
            ->selectRaw('SUM(hit_count) as total_hits')
            ->selectRaw('AVG(hit_count) as avg_hits')
            ->selectRaw('COUNT(CASE WHEN compressed = 1 THEN 1 END) as compressed_entries')
            ->groupBy('strategy')
            ->get()
            ->map(function($stat) {
                return [
                    'strategy' => $stat->strategy,
                    'total_entries' => $stat->total_entries,
                    'total_size' => $stat->total_size,
                    'avg_size' => round($stat->avg_size, 2),
                    'total_hits' => $stat->total_hits,
                    'avg_hits' => round($stat->avg_hits, 2),
                    'compressed_entries' => $stat->compressed_entries,
                    'compression_rate' => round(($stat->compressed_entries / $stat->total_entries) * 100, 2)
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene le statistiche aggregate per tag
     */
    public static function getStatsByTag(): array
    {
        $entries = static::all();
        $tagStats = [];
        
        foreach ($entries as $entry) {
            foreach ($entry->tags ?? [] as $tag) {
                if (!isset($tagStats[$tag])) {
                    $tagStats[$tag] = [
                        'tag' => $tag,
                        'total_entries' => 0,
                        'total_size' => 0,
                        'total_hits' => 0
                    ];
                }
                
                $tagStats[$tag]['total_entries']++;
                $tagStats[$tag]['total_size'] += $entry->size;
                $tagStats[$tag]['total_hits'] += $entry->hit_count;
            }
        }
        
        return array_values($tagStats);
    }

    /**
     * Ottiene le statistiche di dimensione
     */
    public static function getSizeStats(): array
    {
        $stats = static::selectRaw('
            COUNT(*) as total_entries,
            SUM(size) as total_size,
            AVG(size) as avg_size,
            MIN(size) as min_size,
            MAX(size) as max_size,
            COUNT(CASE WHEN compressed = 1 THEN 1 END) as compressed_entries
        ')->first();

        return [
            'total_entries' => $stats->total_entries,
            'total_size' => $stats->total_size,
            'avg_size' => round($stats->avg_size, 2),
            'min_size' => $stats->min_size,
            'max_size' => $stats->max_size,
            'compressed_entries' => $stats->compressed_entries,
            'compression_rate' => $stats->total_entries > 0 ? round(($stats->compressed_entries / $stats->total_entries) * 100, 2) : 0
        ];
    }

    /**
     * Ottiene le statistiche di utilizzo
     */
    public static function getUsageStats(): array
    {
        $stats = static::selectRaw('
            COUNT(*) as total_entries,
            SUM(hit_count) as total_hits,
            AVG(hit_count) as avg_hits,
            MAX(hit_count) as max_hits,
            COUNT(CASE WHEN hit_count = 0 THEN 1 END) as unused_entries
        ')->first();

        return [
            'total_entries' => $stats->total_entries,
            'total_hits' => $stats->total_hits,
            'avg_hits' => round($stats->avg_hits, 2),
            'max_hits' => $stats->max_hits,
            'unused_entries' => $stats->unused_entries,
            'utilization_rate' => $stats->total_entries > 0 ? round((($stats->total_entries - $stats->unused_entries) / $stats->total_entries) * 100, 2) : 0
        ];
    }

    /**
     * Pulisce le chiavi scadute
     */
    public static function cleanExpired(): int
    {
        $expiredCount = static::expired()->count();
        static::expired()->delete();
        
        return $expiredCount;
    }

    /**
     * Pulisce le chiavi inutilizzate
     */
    public static function cleanUnused(int $days = 7): int
    {
        $cutoffDate = now()->subDays($days);
        $unusedCount = static::where('hit_count', 0)
            ->where('created_at', '<', $cutoffDate)
            ->count();
        
        static::where('hit_count', 0)
            ->where('created_at', '<', $cutoffDate)
            ->delete();
        
        return $unusedCount;
    }

    /**
     * Ottiene le chiavi più problematiche
     */
    public static function getProblematicKeys(): array
    {
        return static::where(function($query) {
            $query->where('hit_count', 0)
                  ->orWhere('size', '>', 1024 * 1024) // > 1MB
                  ->orWhere('expires_at', '<', now()->addMinutes(5));
        })
        ->orderBy('hit_count', 'asc')
        ->orderBy('size', 'desc')
        ->limit(20)
        ->get()
        ->map(function($entry) {
            return [
                'key' => $entry->original_key,
                'strategy' => $entry->strategy,
                'hit_count' => $entry->hit_count,
                'size' => $entry->formatted_size,
                'expires_at' => $entry->expires_at,
                'issues' => array_filter([
                    $entry->hit_count == 0 ? 'unused' : null,
                    $entry->size > 1024 * 1024 ? 'large' : null,
                    $entry->isExpiringSoon() ? 'expiring' : null
                ])
            ];
        })
        ->toArray();
    }
}
