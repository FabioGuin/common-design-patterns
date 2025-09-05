<?php

namespace App\ValueObjects;

/**
 * Classe base per Value Object
 * 
 * Fornisce funzionalità comuni per tutti i Value Object:
 * - Immutabilità
 * - Confronto per valore
 * - Serializzazione
 */
abstract class ValueObject
{
    /**
     * Confronta due Value Object per valore
     */
    public function equals(ValueObject $other): bool
    {
        if (get_class($this) !== get_class($other)) {
            return false;
        }

        return $this->getValue() === $other->getValue();
    }

    /**
     * Restituisce il valore primitivo del Value Object
     */
    abstract public function getValue(): mixed;

    /**
     * Restituisce una rappresentazione stringa del Value Object
     */
    public function __toString(): string
    {
        return (string) $this->getValue();
    }

    /**
     * Serializza il Value Object per JSON
     */
    public function jsonSerialize(): mixed
    {
        return $this->getValue();
    }

    /**
     * Restituisce l'hash del Value Object per confronti
     */
    public function hashCode(): string
    {
        return md5(serialize($this->getValue()));
    }
}
