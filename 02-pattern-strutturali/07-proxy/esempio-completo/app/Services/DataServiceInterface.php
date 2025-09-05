<?php

namespace App\Services;

interface DataServiceInterface
{
    /**
     * Ottiene i dati di un utente specifico
     */
    public function getUserData(int $userId): array;
    
    /**
     * Ottiene tutti gli utenti
     */
    public function getAllUsers(): array;
    
    /**
     * Ottiene i post di un utente
     */
    public function getUserPosts(int $userId): array;
    
    /**
     * Ottiene i commenti di un post
     */
    public function getPostComments(int $postId): array;
}
