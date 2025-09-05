<?php

namespace App\Specifications\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface SpecificationInterface
{
    /**
     * Verifica se l'entità soddisfa la specifica
     */
    public function isSatisfiedBy($entity): bool;

    /**
     * Converte la specifica in una query builder
     */
    public function toQuery(): Builder;

    /**
     * Combina questa specifica con un'altra usando AND
     */
    public function and(SpecificationInterface $specification): SpecificationInterface;

    /**
     * Combina questa specifica con un'altra usando OR
     */
    public function or(SpecificationInterface $specification): SpecificationInterface;

    /**
     * Nega questa specifica usando NOT
     */
    public function not(): SpecificationInterface;

    /**
     * Ottiene una descrizione della specifica
     */
    public function getDescription(): string;

    /**
     * Verifica se la specifica è vuota
     */
    public function isEmpty(): bool;
}
