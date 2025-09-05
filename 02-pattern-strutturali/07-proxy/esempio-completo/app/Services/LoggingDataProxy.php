<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LoggingDataProxy implements DataServiceInterface
{
    private DataServiceInterface $dataService;
    
    public function __construct(DataServiceInterface $dataService)
    {
        $this->dataService = $dataService;
    }
    
    /**
     * Ottiene i dati di un utente specifico con logging
     */
    public function getUserData(int $userId): array
    {
        $startTime = microtime(true);
        
        Log::info("LoggingDataProxy: Starting getUserData for user ID {$userId}");
        
        try {
            $result = $this->dataService->getUserData($userId);
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info("LoggingDataProxy: Successfully fetched user data for ID {$userId} in {$executionTime}ms");
            
            return $result;
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::error("LoggingDataProxy: Error fetching user data for ID {$userId} in {$executionTime}ms: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Ottiene tutti gli utenti con logging
     */
    public function getAllUsers(): array
    {
        $startTime = microtime(true);
        
        Log::info("LoggingDataProxy: Starting getAllUsers");
        
        try {
            $result = $this->dataService->getAllUsers();
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info("LoggingDataProxy: Successfully fetched all users in {$executionTime}ms");
            
            return $result;
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::error("LoggingDataProxy: Error fetching all users in {$executionTime}ms: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Ottiene i post di un utente con logging
     */
    public function getUserPosts(int $userId): array
    {
        $startTime = microtime(true);
        
        Log::info("LoggingDataProxy: Starting getUserPosts for user ID {$userId}");
        
        try {
            $result = $this->dataService->getUserPosts($userId);
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info("LoggingDataProxy: Successfully fetched posts for user ID {$userId} in {$executionTime}ms");
            
            return $result;
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::error("LoggingDataProxy: Error fetching posts for user ID {$userId} in {$executionTime}ms: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Ottiene i commenti di un post con logging
     */
    public function getPostComments(int $postId): array
    {
        $startTime = microtime(true);
        
        Log::info("LoggingDataProxy: Starting getPostComments for post ID {$postId}");
        
        try {
            $result = $this->dataService->getPostComments($postId);
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info("LoggingDataProxy: Successfully fetched comments for post ID {$postId} in {$executionTime}ms");
            
            return $result;
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::error("LoggingDataProxy: Error fetching comments for post ID {$postId} in {$executionTime}ms: " . $e->getMessage());
            throw $e;
        }
    }
}
