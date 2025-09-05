<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any comments.
     */
    public function viewAny(User $user): bool
    {
        // Tutti gli utenti autenticati possono vedere la lista dei commenti
        return $user !== null;
    }

    /**
     * Determine whether the user can view the comment.
     */
    public function view(User $user, Comment $comment): bool
    {
        // Tutti possono vedere commenti approvati
        if ($comment->approved) {
            return true;
        }

        // L'autore può vedere i propri commenti
        if ($user->id === $comment->user_id) {
            return true;
        }

        // Admin e moderatori possono vedere tutti i commenti
        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        Log::info('Comment view denied', [
            'user_id' => $user->id,
            'comment_id' => $comment->id,
            'comment_approved' => $comment->approved,
            'comment_author_id' => $comment->user_id
        ]);

        return false;
    }

    /**
     * Determine whether the user can create comments.
     */
    public function create(User $user): bool
    {
        // Solo utenti autenticati possono creare commenti
        $canCreate = $user !== null;

        if (!$canCreate) {
            Log::info('Comment creation denied - user not authenticated');
        }

        return $canCreate;
    }

    /**
     * Determine whether the user can update the comment.
     */
    public function update(User $user, Comment $comment): bool
    {
        // Admin può modificare tutto
        if ($user->isAdmin()) {
            return true;
        }

        // L'autore può modificare i propri commenti se non sono approvati
        if ($user->id === $comment->user_id && !$comment->approved) {
            return true;
        }

        // Moderatori possono modificare commenti non approvati
        if ($user->isModerator() && !$comment->approved) {
            return true;
        }

        Log::info('Comment update denied', [
            'user_id' => $user->id,
            'comment_id' => $comment->id,
            'comment_author_id' => $comment->user_id,
            'comment_approved' => $comment->approved,
            'user_role' => $user->role
        ]);

        return false;
    }

    /**
     * Determine whether the user can delete the comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // Admin può eliminare tutto
        if ($user->isAdmin()) {
            return true;
        }

        // L'autore può eliminare i propri commenti
        if ($user->id === $comment->user_id) {
            return true;
        }

        // Moderatori possono eliminare commenti
        if ($user->isModerator()) {
            return true;
        }

        Log::info('Comment deletion denied', [
            'user_id' => $user->id,
            'comment_id' => $comment->id,
            'comment_author_id' => $comment->user_id,
            'user_role' => $user->role
        ]);

        return false;
    }

    /**
     * Determine whether the user can restore the comment.
     */
    public function restore(User $user, Comment $comment): bool
    {
        // Solo admin può ripristinare commenti
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the comment.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        // Solo admin può eliminare definitivamente
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can approve the comment.
     */
    public function approve(User $user, Comment $comment): bool
    {
        // Solo admin e moderatori possono approvare commenti
        $canApprove = $user->isAdmin() || $user->isModerator();

        if (!$canApprove) {
            Log::info('Comment approval denied', [
                'user_id' => $user->id,
                'comment_id' => $comment->id,
                'user_role' => $user->role
            ]);
        }

        return $canApprove;
    }

    /**
     * Determine whether the user can reject the comment.
     */
    public function reject(User $user, Comment $comment): bool
    {
        // Solo admin e moderatori possono rifiutare commenti
        $canReject = $user->isAdmin() || $user->isModerator();

        if (!$canReject) {
            Log::info('Comment rejection denied', [
                'user_id' => $user->id,
                'comment_id' => $comment->id,
                'user_role' => $user->role
            ]);
        }

        return $canReject;
    }

    /**
     * Determine whether the user can reply to the comment.
     */
    public function reply(User $user, Comment $comment): bool
    {
        // Solo utenti autenticati possono rispondere
        if (!$user) {
            return false;
        }

        // Non si può rispondere a commenti non approvati
        if (!$comment->approved) {
            return false;
        }

        // Non si può rispondere a commenti eliminati
        if ($comment->trashed()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can moderate the comment.
     */
    public function moderate(User $user, Comment $comment): bool
    {
        // Solo admin e moderatori possono moderare
        return $user->isAdmin() || $user->isModerator();
    }

    /**
     * Determine whether the user can view the comment's replies.
     */
    public function viewReplies(User $user, Comment $comment): bool
    {
        // Se il commento è approvato, tutti possono vedere le risposte
        if ($comment->approved) {
            return true;
        }

        // Altrimenti, stessa logica di view
        return $this->view($user, $comment);
    }

    /**
     * Determine whether the user can flag the comment.
     */
    public function flag(User $user, Comment $comment): bool
    {
        // Solo utenti autenticati possono segnalare
        if (!$user) {
            return false;
        }

        // Non si può segnalare i propri commenti
        if ($user->id === $comment->user_id) {
            return false;
        }

        // Non si può segnalare commenti già approvati
        if ($comment->approved) {
            return false;
        }

        return true;
    }
}
