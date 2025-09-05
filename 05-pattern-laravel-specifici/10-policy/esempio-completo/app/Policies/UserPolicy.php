<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        // Solo admin e moderatori possono vedere la lista degli utenti
        return $user->isAdmin() || $user->isModerator();
    }

    /**
     * Determine whether the user can view the user.
     */
    public function view(User $user, User $model): bool
    {
        // L'utente può vedere il proprio profilo
        if ($user->id === $model->id) {
            return true;
        }

        // Admin e moderatori possono vedere tutti i profili
        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        // Gli utenti possono vedere profili pubblici
        if ($model->isPublic()) {
            return true;
        }

        Log::info('User view denied', [
            'user_id' => $user->id,
            'target_user_id' => $model->id,
            'target_user_public' => $model->isPublic(),
            'user_role' => $user->role
        ]);

        return false;
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        // Solo admin può creare utenti
        $canCreate = $user->isAdmin();

        if (!$canCreate) {
            Log::info('User creation denied', [
                'user_id' => $user->id,
                'user_role' => $user->role
            ]);
        }

        return $canCreate;
    }

    /**
     * Determine whether the user can update the user.
     */
    public function update(User $user, User $model): bool
    {
        // L'utente può aggiornare il proprio profilo
        if ($user->id === $model->id) {
            return true;
        }

        // Admin può aggiornare tutti i profili
        if ($user->isAdmin()) {
            return true;
        }

        // Moderatori possono aggiornare profili di utenti normali
        if ($user->isModerator() && $model->isUser()) {
            return true;
        }

        Log::info('User update denied', [
            'user_id' => $user->id,
            'target_user_id' => $model->id,
            'target_user_role' => $model->role,
            'user_role' => $user->role
        ]);

        return false;
    }

    /**
     * Determine whether the user can delete the user.
     */
    public function delete(User $user, User $model): bool
    {
        // Non si può eliminare se stessi
        if ($user->id === $model->id) {
            return false;
        }

        // Solo admin può eliminare utenti
        if ($user->isAdmin()) {
            return true;
        }

        Log::info('User deletion denied', [
            'user_id' => $user->id,
            'target_user_id' => $model->id,
            'target_user_role' => $model->role,
            'user_role' => $user->role
        ]);

        return false;
    }

    /**
     * Determine whether the user can restore the user.
     */
    public function restore(User $user, User $model): bool
    {
        // Solo admin può ripristinare utenti
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the user.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Solo admin può eliminare definitivamente
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can change the user's role.
     */
    public function changeRole(User $user, User $model): bool
    {
        // Solo admin può cambiare i ruoli
        if (!$user->isAdmin()) {
            Log::info('Role change denied - not admin', [
                'user_id' => $user->id,
                'target_user_id' => $model->id,
                'user_role' => $user->role
            ]);
            return false;
        }

        // Non si può cambiare il proprio ruolo
        if ($user->id === $model->id) {
            Log::info('Role change denied - self change', [
                'user_id' => $user->id,
                'target_user_id' => $model->id
            ]);
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can manage the user's permissions.
     */
    public function managePermissions(User $user, User $model): bool
    {
        // Solo admin può gestire i permessi
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the user's posts.
     */
    public function viewPosts(User $user, User $model): bool
        {
        // L'utente può vedere i propri post
        if ($user->id === $model->id) {
            return true;
        }

        // Admin e moderatori possono vedere tutti i post
        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        // Gli utenti possono vedere post pubblici di altri
        return true;
    }

    /**
     * Determine whether the user can view the user's comments.
     */
    public function viewComments(User $user, User $model): bool
    {
        // L'utente può vedere i propri commenti
        if ($user->id === $model->id) {
            return true;
        }

        // Admin e moderatori possono vedere tutti i commenti
        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        // Gli utenti possono vedere commenti approvati di altri
        return true;
    }

    /**
     * Determine whether the user can ban the user.
     */
    public function ban(User $user, User $model): bool
    {
        // Solo admin può bannare utenti
        if (!$user->isAdmin()) {
            return false;
        }

        // Non si può bannare se stessi
        if ($user->id === $model->id) {
            return false;
        }

        // Non si può bannare altri admin
        if ($model->isAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can unban the user.
     */
    public function unban(User $user, User $model): bool
    {
        // Solo admin può sbannare utenti
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the user's profile.
     */
    public function viewProfile(User $user, User $model): bool
    {
        // L'utente può vedere il proprio profilo
        if ($user->id === $model->id) {
            return true;
        }

        // Admin e moderatori possono vedere tutti i profili
        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        // Gli utenti possono vedere profili pubblici
        return $model->isPublic();
    }

    /**
     * Determine whether the user can edit the user's profile.
     */
    public function editProfile(User $user, User $model): bool
    {
        // L'utente può modificare il proprio profilo
        if ($user->id === $model->id) {
            return true;
        }

        // Admin può modificare tutti i profili
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }
}
