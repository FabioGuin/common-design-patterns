<?php

namespace App\Services\Iterators;

interface IteratorInterface
{
    public function current(): mixed;
    public function next(): void;
    public function key(): mixed;
    public function valid(): bool;
    public function rewind(): void;
}
