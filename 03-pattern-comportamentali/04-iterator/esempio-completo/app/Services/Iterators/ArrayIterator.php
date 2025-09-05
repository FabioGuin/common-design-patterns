<?php

namespace App\Services\Iterators;

class ArrayIterator implements IteratorInterface
{
    private array $items;
    private int $position = 0;
    
    public function __construct(array $items)
    {
        $this->items = $items;
    }
    
    public function current(): mixed
    {
        return $this->items[$this->position];
    }
    
    public function next(): void
    {
        $this->position++;
    }
    
    public function key(): int
    {
        return $this->position;
    }
    
    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }
    
    public function rewind(): void
    {
        $this->position = 0;
    }
}
