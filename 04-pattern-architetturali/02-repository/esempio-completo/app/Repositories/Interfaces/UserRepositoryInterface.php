<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Recupera tutti gli utenti
     */
    public function findAll(): Collection;

    /**
     * Recupera un utente per ID
     */
    public function findById(int $id): ?User;

    /**
     * Recupera un utente per email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Recupera utenti per ruolo
     */
    public function findByRole(string $role): Collection;

    /**
     * Recupera utenti attivi
     */
    public function findActive(): Collection;

    /**
     * Recupera utenti inattivi
     */
    public function findInactive(): Collection;

    /**
     * Cerca utenti per termine
     */
    public function search(string $term): Collection;

    /**
     * Recupera utenti con articoli
     */
    public function findWithArticles(): Collection;

    /**
     * Recupera utenti senza articoli
     */
    public function findWithoutArticles(): Collection;

    /**
     * Recupera utenti con paginazione
     */
    public function paginate(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator;

    /**
     * Conta il numero totale di utenti
     */
    public function count(): int;

    /**
     * Conta utenti attivi
     */
    public function countActive(): int;

    /**
     * Conta utenti per ruolo
     */
    public function countByRole(string $role): int;

    /**
     * Crea un nuovo utente
     */
    public function create(array $data): User;

    /**
     * Aggiorna un utente esistente
     */
    public function update(int $id, array $data): bool;

    /**
     * Elimina un utente
     */
    public function delete(int $id): bool;

    /**
     * Attiva un utente
     */
    public function activate(int $id): bool;

    /**
     * Disattiva un utente
     */
    public function deactivate(int $id): bool;

    /**
     * Cambia il ruolo di un utente
     */
    public function changeRole(int $id, string $role): bool;

    /**
     * Recupera utenti con statistiche
     */
    public function findWithStats(): Collection;

    /**
     * Recupera utenti più attivi
     */
    public function findMostActive(int $limit = 10): Collection;

    /**
     * Recupera utenti recenti
     */
    public function findRecent(int $limit = 10): Collection;
}
