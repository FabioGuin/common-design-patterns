<?php

namespace App\Services;

class MenuCategory implements MenuComponentInterface
{
    private string $name;
    private string $description;
    private array $children = [];

    public function __construct(string $name, string $description = '')
    {
        $this->name = $name;
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
     * Ottiene il prezzo dell'elemento (0 per categorie)
     */
    public function getPrice(): float
    {
        return 0.0;
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
        return true;
    }

    /**
     * Aggiunge un elemento figlio
     */
    public function add(MenuComponentInterface $component): void
    {
        $this->children[] = $component;
    }

    /**
     * Rimuove un elemento figlio
     */
    public function remove(MenuComponentInterface $component): void
    {
        $key = array_search($component, $this->children, true);
        if ($key !== false) {
            unset($this->children[$key]);
            $this->children = array_values($this->children); // Reindex array
        }
    }

    /**
     * Ottiene tutti gli elementi figli
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Ottiene il numero totale di elementi (inclusi i figli)
     */
    public function getTotalCount(): int
    {
        $count = 1; // Conta la categoria stessa
        foreach ($this->children as $child) {
            $count += $child->getTotalCount();
        }
        return $count;
    }

    /**
     * Ottiene il prezzo totale (somma dei prezzi dei figli)
     */
    public function getTotalPrice(): float
    {
        $total = 0.0;
        foreach ($this->children as $child) {
            $total += $child->getTotalPrice();
        }
        return $total;
    }

    /**
     * Cerca un elemento per nome (ricorsivamente)
     */
    public function findByName(string $name): ?MenuComponentInterface
    {
        // Cerca prima nella categoria stessa
        if (strtolower($this->name) === strtolower($name)) {
            return $this;
        }

        // Cerca nei figli
        foreach ($this->children as $child) {
            $found = $child->findByName($name);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    /**
     * Ottiene la rappresentazione in array dell'elemento
     */
    public function toArray(): array
    {
        $childrenArray = [];
        foreach ($this->children as $child) {
            $childrenArray[] = $child->toArray();
        }

        return [
            'type' => 'category',
            'name' => $this->name,
            'price' => 0.0,
            'description' => $this->description,
            'is_category' => true,
            'children_count' => count($this->children),
            'total_count' => $this->getTotalCount(),
            'total_price' => $this->getTotalPrice(),
            'children' => $childrenArray,
        ];
    }

    /**
     * Ottiene solo le voci del menu (non le categorie)
     */
    public function getMenuItems(): array
    {
        $items = [];
        foreach ($this->children as $child) {
            if ($child instanceof MenuItem) {
                $items[] = $child;
            } elseif ($child instanceof MenuCategory) {
                $items = array_merge($items, $child->getMenuItems());
            }
        }
        return $items;
    }

    /**
     * Ottiene solo le sottocategorie
     */
    public function getSubCategories(): array
    {
        $categories = [];
        foreach ($this->children as $child) {
            if ($child instanceof MenuCategory) {
                $categories[] = $child;
            }
        }
        return $categories;
    }
}
