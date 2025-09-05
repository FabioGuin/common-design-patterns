<?php

namespace App\Specifications\Base;

use App\Specifications\Interfaces\SpecificationInterface;
use App\Specifications\Composite\AndSpecification;
use App\Specifications\Composite\OrSpecification;
use App\Specifications\Composite\NotSpecification;

abstract class BaseSpecification implements SpecificationInterface
{
    /**
     * Combina questa specifica con un'altra usando AND
     */
    public function and(SpecificationInterface $specification): SpecificationInterface
    {
        return new AndSpecification($this, $specification);
    }

    /**
     * Combina questa specifica con un'altra usando OR
     */
    public function or(SpecificationInterface $specification): SpecificationInterface
    {
        return new OrSpecification($this, $specification);
    }

    /**
     * Nega questa specifica usando NOT
     */
    public function not(): SpecificationInterface
    {
        return new NotSpecification($this);
    }

    /**
     * Verifica se la specifica è vuota
     */
    public function isEmpty(): bool
    {
        return false;
    }

    /**
     * Ottiene una descrizione della specifica
     */
    public function getDescription(): string
    {
        return static::class;
    }

    /**
     * Converte la specifica in stringa
     */
    public function __toString(): string
    {
        return $this->getDescription();
    }

    /**
     * Serializza la specifica
     */
    public function __serialize(): array
    {
        return [
            'class' => static::class,
            'data' => $this->getSerializableData()
        ];
    }

    /**
     * Deserializza la specifica
     */
    public function __unserialize(array $data): void
    {
        // Implementazione per deserializzazione
    }

    /**
     * Ottiene i dati serializzabili della specifica
     */
    protected function getSerializableData(): array
    {
        return [];
    }

    /**
     * Verifica se due specifiche sono uguali
     */
    public function equals(SpecificationInterface $other): bool
    {
        return static::class === get_class($other) && 
               $this->getSerializableData() === $other->getSerializableData();
    }

    /**
     * Ottiene l'hash della specifica
     */
    public function getHash(): string
    {
        return md5(static::class . serialize($this->getSerializableData()));
    }
}
