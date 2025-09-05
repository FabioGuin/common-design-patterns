<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any posts.
     */
    public function viewAny(User $user): bool
    {
        // Tutti gli utenti autenticati possono vedere la lista dei post
        return $user !== null;
    }

    /**
     * Determine whether the user can view the post.
     */
    public function view(User $user, Post $post): bool
    {
        // Tutti possono vedere post pubblicati
        if ($post->status === 'published') {
            return true;
        }

        // Solo l'autore può vedere i propri post non pubblicati
        if ($user->id === $post->user_id) {
            return true;
        }

        // Admin e moderatori possono vedere tutti i post
        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        Log::info('Post view denied', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'post_status' => $post->status,
            'post_author_id' => $post->user_id
        ]);

        return false;
    }

    /**
     * Determine whether the user can create posts.
     */
    public function create(User $user): bool
    {
        // Solo utenti autenticati possono creare post
        $canCreate = $user !== null;

        if (!$canCreate) {
            Log::info('Post creation denied - user not authenticated');
        }

        return $canCreate;
    }

    /**
     * Determine whether the user can update the post.
     */
    public function update(User $user, Post $post): bool
    {
        // Admin può modificare tutto
        if ($user->isAdmin()) {
            return true;
        }

        // L'autore può modificare i propri post
        if ($user->id === $post->user_id) {
            return true;
        }

        // Moderatori possono modificare post di altri utenti
        if ($user->isModerator()) {
            return true;
        }

        Log::info('Post update denied', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'post_author_id' => $post->user_id,
            'user_role' => $user->role
        ]);

        return false;
    }

    /**
     * Determine whether the user can delete the post.
     */
    public function delete(User $user, Post $post): bool
    {
        // Solo admin può eliminare post
        if ($user->isAdmin()) {
            return true;
        }

        // L'autore può eliminare i propri post se non sono pubblicati
        if ($user->id === $post->user_id && $post->status !== 'published') {
            return true;
        }

        Log::info('Post deletion denied', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'post_author_id' => $post->user_id,
            'post_status' => $post->status,
            'user_role' => $user->role
        ]);

        return false;
    }

    /**
     * Determine whether the user can restore the post.
     */
    public function restore(User $user, Post $post): bool
    {
        // Solo admin può ripristinare post
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the post.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        // Solo admin può eliminare definitivamente
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can publish the post.
     */
    public function publish(User $user, Post $post): bool
    {
        // Admin e moderatori possono pubblicare
        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        // L'autore può pubblicare i propri post
        if ($user->id === $post->user_id) {
            return true;
        }

        Log::info('Post publish denied', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'post_author_id' => $post->user_id,
            'user_role' => $user->role
        ]);

        return false;
    }

    /**
     * Determine whether the user can archive the post.
     */
    public function archive(User $user, Post $post): bool
    {
        // Admin e moderatori possono archiviare
        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        // L'autore può archiviare i propri post
        if ($user->id === $post->user_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the post's comments.
     */
    public function viewComments(User $user, Post $post): bool
    {
        // Se il post è pubblico, tutti possono vedere i commenti
        if ($post->status === 'published') {
            return true;
        }

        // Altrimenti, stessa logica di view
        return $this->view($user, $post);
    }

    /**
     * Determine whether the user can moderate the post.
     */
    public function moderate(User $user, Post $post): bool
    {
        // Solo admin e moderatori possono moderare
        return $user->isAdmin() || $user->isModerator();
    }
}
