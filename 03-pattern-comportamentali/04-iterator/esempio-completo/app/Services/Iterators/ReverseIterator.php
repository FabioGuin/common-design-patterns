<?php

namespace App\Services\Iterators;

class ReverseIterator implements IteratorInterface
{
    private array $items;
    private int $position;
    
    public function __construct(array $items)
    {
        $this->items = array_reverse($items);
        $this->position = 0;
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
