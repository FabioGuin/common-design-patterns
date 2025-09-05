<?php

namespace App\Services;

class MenuItem implements MenuComponentInterface
{
    private string $name;
    private float $price;
    private string $description;

    public function __construct(string $name, float $price, string $description = '')
    {
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
    }

    /**
     * Ottiene il nome dell'elemento
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Ottiene il prezzo dell'elemento
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * Ottiene la descrizione dell'elemento
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Verifica se l'elemento è una categoria
     */
    public function isCategory(): bool
    {
        return false;
    }

    /**
     * Aggiunge un elemento figlio (non supportato per voci singole)
     */
    public function add(MenuComponentInterface $component): void
    {
        throw new \Exception('Cannot add children to a menu item');
    }

    /**
     * Rimuove un elemento figlio (non supportato per voci singole)
     */
    public function remove(MenuComponentInterface $component): void
    {
        throw new \Exception('Cannot remove children from a menu item');
    }

    /**
     * Ottiene tutti gli elementi figli (vuoto per voci singole)
     */
    public function getChildren(): array
    {
        return [];
    }

    /**
     * Ottiene il numero totale di elementi (sempre 1 per voci singole)
     */
    public function getTotalCount(): int
    {
        return 1;
    }

    /**
     * Ottiene il prezzo totale (uguale al prezzo dell'elemento)
     */
    public function getTotalPrice(): float
    {
        return $this->price;
    }

    /**
     * Cerca un elemento per nome
     */
    public function findByName(string $name): ?MenuComponentInterface
    {
        return strtolower($this->name) === strtolower($name) ? $this : null;
    }

    /**
     * Ottiene la rappresentazione in array dell'elemento
     */
    public function toArray(): array
    {
        return [
            'type' => 'item',
            'name' => $this->name,
            'price' => $this->price,
            'description' => $this->description,
            'is_category' => false,
            'children_count' => 0,
            'total_count' => 1,
            'total_price' => $this->price,
        ];
    }
}
