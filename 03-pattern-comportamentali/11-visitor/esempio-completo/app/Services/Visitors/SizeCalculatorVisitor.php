<?php

namespace App\Services\Visitors;

class SizeCalculatorVisitor implements VisitorInterface
{
    private int $totalSize = 0;
    
    public function visitFile(object $file): int
    {
        $size = $file->getSize();
        $this->totalSize += $size;
        return $size;
    }
    
    public function visitDirectory(object $directory): int
    {
        $directorySize = 0;
        foreach ($directory->getChildren() as $child) {
            $directorySize += $child->accept($this);
        }
        return $directorySize;
    }
    
    public function getTotalSize(): int
    {
        return $this->totalSize;
    }
}
