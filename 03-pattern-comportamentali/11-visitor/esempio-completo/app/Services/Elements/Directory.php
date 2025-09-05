<?php

namespace App\Services\Elements;

class Directory implements ElementInterface
{
    private string $name;
    private array $children = [];
    
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    
    public function addChild(ElementInterface $element): void
    {
        $this->children[] = $element;
    }
    
    public function accept(object $visitor): mixed
    {
        return $visitor->visitDirectory($this);
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getChildren(): array
    {
        return $this->children;
    }
}
