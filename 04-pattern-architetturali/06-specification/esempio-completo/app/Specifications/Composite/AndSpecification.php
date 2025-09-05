<?php

namespace App\Specifications\Composite;

use App\Specifications\Interfaces\SpecificationInterface;
use App\Specifications\Base\BaseSpecification;
use Illuminate\Database\Eloquent\Builder;

class AndSpecification extends BaseSpecification
{
    public function __construct(
        private SpecificationInterface $left,
        private SpecificationInterface $right
    ) {}

    /**
     * Verifica se l'entità soddisfa entrambe le specifiche
     */
    public function isSatisfiedBy($entity): bool
    {
        return $this->left->isSatisfiedBy($entity) && $this->right->isSatisfiedBy($entity);
    }

    /**
     * Converte la specifica in una query builder
     */
    public function toQuery(): Builder
    {
        $leftQuery = $this->left->toQuery();
        $rightQuery = $this->right->toQuery();

        // Applica le condizioni della specifica destra alla query sinistra
        return $leftQuery->where(function ($query) use ($rightQuery) {
            $query->where(function ($subQuery) use ($rightQuery) {
                // Copia le condizioni WHERE dalla query destra
                $rightWheres = $rightQuery->getQuery()->wheres;
                foreach ($rightWheres as $where) {
                    $subQuery->where($where['column'], $where['operator'], $where['value']);
                }
            });
        });
    }

    /**
     * Ottiene una descrizione della specifica
     */
    public function getDescription(): string
    {
        return "({$this->left->getDescription()}) AND ({$this->right->getDescription()})";
    }

    /**
     * Ottiene i dati serializzabili della specifica
     */
    protected function getSerializableData(): array
    {
        return [
            'left' => $this->left,
            'right' => $this->right
        ];
    }

    /**
     * Ottiene la specifica sinistra
     */
    public function getLeft(): SpecificationInterface
    {
        return $this->left;
    }

    /**
     * Ottiene la specifica destra
     */
    public function getRight(): SpecificationInterface
    {
        return $this->right;
    }

    /**
     * Verifica se la specifica è vuota
     */
    public function isEmpty(): bool
    {
        return $this->left->isEmpty() && $this->right->isEmpty();
    }

    /**
     * Ottiene tutte le specifiche figlie
     */
    public function getSpecifications(): array
    {
        return [$this->left, $this->right];
    }

    /**
     * Ottiene il numero di specifiche figlie
     */
    public function getSpecificationCount(): int
    {
        return 2;
    }
}
