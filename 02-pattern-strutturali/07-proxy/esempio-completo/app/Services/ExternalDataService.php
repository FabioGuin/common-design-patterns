<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ExternalDataService implements DataServiceInterface
{
    private Client $client;
    private string $baseUrl;
    
    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = config('services.external_api.url', 'https://jsonplaceholder.typicode.com');
    }
    
    /**
     * Ottiene i dati di un utente specifico
     */
    public function getUserData(int $userId): array
    {
        try {
            $response = $this->client->get("{$this->baseUrl}/users/{$userId}");
            $data = json_decode($response->getBody()->getContents(), true);
            
            Log::info("ExternalDataService: Fetched user data for ID {$userId}");
            
            return $data;
        } catch (GuzzleException $e) {
            Log::error("ExternalDataService: Error fetching user data for ID {$userId}: " . $e->getMessage());
            throw new \Exception("Failed to fetch user data: " . $e->getMessage());
        }
    }
    
    /**
     * Ottiene tutti gli utenti
     */
    public function getAllUsers(): array
    {
        try {
            $response = $this->client->get("{$this->baseUrl}/users");
            $data = json_decode($response->getBody()->getContents(), true);
            
            Log::info("ExternalDataService: Fetched all users data");
            
            return $data;
        } catch (GuzzleException $e) {
            Log::error("ExternalDataService: Error fetching all users: " . $e->getMessage());
            throw new \Exception("Failed to fetch all users: " . $e->getMessage());
        }
    }
    
    /**
     * Ottiene i post di un utente
     */
    public function getUserPosts(int $userId): array
    {
        try {
            $response = $this->client->get("{$this->baseUrl}/users/{$userId}/posts");
            $data = json_decode($response->getBody()->getContents(), true);
            
            Log::info("ExternalDataService: Fetched posts for user ID {$userId}");
            
            return $data;
        } catch (GuzzleException $e) {
            Log::error("ExternalDataService: Error fetching posts for user ID {$userId}: " . $e->getMessage());
            throw new \Exception("Failed to fetch user posts: " . $e->getMessage());
        }
    }
    
    /**
     * Ottiene i commenti di un post
     */
    public function getPostComments(int $postId): array
    {
        try {
            $response = $this->client->get("{$this->baseUrl}/posts/{$postId}/comments");
            $data = json_decode($response->getBody()->getContents(), true);
            
            Log::info("ExternalDataService: Fetched comments for post ID {$postId}");
            
            return $data;
        } catch (GuzzleException $e) {
            Log::error("ExternalDataService: Error fetching comments for post ID {$postId}: " . $e->getMessage());
            throw new \Exception("Failed to fetch post comments: " . $e->getMessage());
        }
    }
}
