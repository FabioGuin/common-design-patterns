<?php

namespace App\Repositories\Interfaces;

use App\Models\Article;
use Illuminate\Database\Eloquent\Collection;

interface ArticleRepositoryInterface
{
    /**
     * Recupera tutti gli articoli
     */
    public function findAll(): Collection;

    /**
     * Recupera un articolo per ID
     */
    public function findById(int $id): ?Article;

    /**
     * Recupera articoli per autore
     */
    public function findByAuthor(int $authorId): Collection;

    /**
     * Recupera articoli pubblicati
     */
    public function findPublished(): Collection;

    /**
     * Recupera articoli in bozza
     */
    public function findDrafts(): Collection;

    /**
     * Cerca articoli per termine
     */
    public function search(string $term): Collection;

    /**
     * Recupera articoli recenti
     */
    public function findRecent(int $limit = 10): Collection;

    /**
     * Recupera articoli per categoria (se implementata)
     */
    public function findByCategory(string $category): Collection;

    /**
     * Recupera articoli con paginazione
     */
    public function paginate(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator;

    /**
     * Conta il numero totale di articoli
     */
    public function count(): int;

    /**
     * Conta articoli pubblicati
     */
    public function countPublished(): int;

    /**
     * Conta articoli in bozza
     */
    public function countDrafts(): int;

    /**
     * Crea un nuovo articolo
     */
    public function create(array $data): Article;

    /**
     * Aggiorna un articolo esistente
     */
    public function update(int $id, array $data): bool;

    /**
     * Elimina un articolo
     */
    public function delete(int $id): bool;

    /**
     * Pubblica un articolo
     */
    public function publish(int $id): bool;

    /**
     * Mette in bozza un articolo
     */
    public function draft(int $id): bool;

    /**
     * Recupera articoli con statistiche
     */
    public function findWithStats(): Collection;

    /**
     * Recupera articoli più popolari
     */
    public function findPopular(int $limit = 5): Collection;

    /**
     * Recupera articoli correlati
     */
    public function findRelated(int $articleId, int $limit = 5): Collection;
}
