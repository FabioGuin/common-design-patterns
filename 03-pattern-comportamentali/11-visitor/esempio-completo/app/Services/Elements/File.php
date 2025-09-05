<?php

namespace App\Services\Elements;

class File implements ElementInterface
{
    private string $name;
    private int $size;
    
    public function __construct(string $name, int $size)
    {
        $this->name = $name;
        $this->size = $size;
    }
    
    public function accept(object $visitor): mixed
    {
        return $visitor->visitFile($this);
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getSize(): int
    {
        return $this->size;
    }
}
