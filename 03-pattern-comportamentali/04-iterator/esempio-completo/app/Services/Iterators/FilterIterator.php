<?php

namespace App\Services\Iterators;

class FilterIterator implements IteratorInterface
{
    private IteratorInterface $iterator;
    private $filter;
    
    public function __construct(IteratorInterface $iterator, callable $filter)
    {
        $this->iterator = $iterator;
        $this->filter = $filter;
    }
    
    public function current(): mixed
    {
        return $this->iterator->current();
    }
    
    public function next(): void
    {
        do {
            $this->iterator->next();
        } while ($this->iterator->valid() && !($this->filter)($this->iterator->current()));
    }
    
    public function key(): mixed
    {
        return $this->iterator->key();
    }
    
    public function valid(): bool
    {
        return $this->iterator->valid() && ($this->filter)($this->iterator->current());
    }
    
    public function rewind(): void
    {
        $this->iterator->rewind();
        if ($this->iterator->valid() && !($this->filter)($this->iterator->current())) {
            $this->next();
        }
    }
}
