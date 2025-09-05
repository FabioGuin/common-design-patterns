<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CachingDataProxy implements DataServiceInterface
{
    private DataServiceInterface $dataService;
    private int $cacheTtl;
    
    public function __construct(DataServiceInterface $dataService)
    {
        $this->dataService = $dataService;
        $this->cacheTtl = config('services.cache_ttl', 3600); // 1 ora di default
    }
    
    /**
     * Ottiene i dati di un utente specifico con caching
     */
    public function getUserData(int $userId): array
    {
        $cacheKey = "user_data_{$userId}";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($userId) {
            return $this->dataService->getUserData($userId);
        });
    }
    
    /**
     * Ottiene tutti gli utenti con caching
     */
    public function getAllUsers(): array
    {
        $cacheKey = "all_users";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->dataService->getAllUsers();
        });
    }
    
    /**
     * Ottiene i post di un utente con caching
     */
    public function getUserPosts(int $userId): array
    {
        $cacheKey = "user_posts_{$userId}";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($userId) {
            return $this->dataService->getUserPosts($userId);
        });
    }
    
    /**
     * Ottiene i commenti di un post con caching
     */
    public function getPostComments(int $postId): array
    {
        $cacheKey = "post_comments_{$postId}";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($postId) {
            return $this->dataService->getPostComments($postId);
        });
    }
    
    /**
     * Invalida la cache per un utente specifico
     */
    public function invalidateUserCache(int $userId): void
    {
        Cache::forget("user_data_{$userId}");
        Cache::forget("user_posts_{$userId}");
    }
    
    /**
     * Invalida tutta la cache
     */
    public function invalidateAllCache(): void
    {
        Cache::flush();
    }
}
