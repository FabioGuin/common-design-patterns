<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * Recupera tutti gli utenti
     */
    public function findAll(): Collection
    {
        return User::all();
    }

    /**
     * Recupera un utente per ID
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Recupera un utente per email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Recupera utenti per ruolo
     */
    public function findByRole(string $role): Collection
    {
        return User::where('role', $role)
                  ->orderBy('created_at', 'desc')
                  ->get();
    }

    /**
     * Recupera utenti attivi
     */
    public function findActive(): Collection
    {
        return User::where('is_active', true)
                  ->orderBy('created_at', 'desc')
                  ->get();
    }

    /**
     * Recupera utenti inattivi
     */
    public function findInactive(): Collection
    {
        return User::where('is_active', false)
                  ->orderBy('created_at', 'desc')
                  ->get();
    }

    /**
     * Cerca utenti per termine
     */
    public function search(string $term): Collection
    {
        return User::where(function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('bio', 'like', "%{$term}%");
        })
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Recupera utenti con articoli
     */
    public function findWithArticles(): Collection
    {
        return User::whereHas('articles')
                  ->withCount('articles')
                  ->orderBy('created_at', 'desc')
                  ->get();
    }

    /**
     * Recupera utenti senza articoli
     */
    public function findWithoutArticles(): Collection
    {
        return User::whereDoesntHave('articles')
                  ->orderBy('created_at', 'desc')
                  ->get();
    }

    /**
     * Recupera utenti con paginazione
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::withCount('articles')
                  ->orderBy('created_at', 'desc')
                  ->paginate($perPage);
    }

    /**
     * Conta il numero totale di utenti
     */
    public function count(): int
    {
        return User::count();
    }

    /**
     * Conta utenti attivi
     */
    public function countActive(): int
    {
        return User::where('is_active', true)->count();
    }

    /**
     * Conta utenti per ruolo
     */
    public function countByRole(string $role): int
    {
        return User::where('role', $role)->count();
    }

    /**
     * Crea un nuovo utente
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Aggiorna un utente esistente
     */
    public function update(int $id, array $data): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        return $user->update($data);
    }

    /**
     * Elimina un utente
     */
    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        return $user->delete();
    }

    /**
     * Attiva un utente
     */
    public function activate(int $id): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        return $user->update(['is_active' => true]);
    }

    /**
     * Disattiva un utente
     */
    public function deactivate(int $id): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        return $user->update(['is_active' => false]);
    }

    /**
     * Cambia il ruolo di un utente
     */
    public function changeRole(int $id, string $role): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        return $user->update(['role' => $role]);
    }

    /**
     * Recupera utenti con statistiche
     */
    public function findWithStats(): Collection
    {
        return User::withCount([
            'articles as total_articles_count',
            'articles as published_articles_count' => function ($query) {
                $query->where('status', 'published');
            }
        ])
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Recupera utenti più attivi
     */
    public function findMostActive(int $limit = 10): Collection
    {
        return User::withCount('articles')
                  ->orderBy('articles_count', 'desc')
                  ->limit($limit)
                  ->get();
    }

    /**
     * Recupera utenti recenti
     */
    public function findRecent(int $limit = 10): Collection
    {
        return User::orderBy('created_at', 'desc')
                  ->limit($limit)
                  ->get();
    }
}
