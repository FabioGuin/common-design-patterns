<?php

namespace App\Services\Visitors;

class FileCountVisitor implements VisitorInterface
{
    private int $fileCount = 0;
    
    public function visitFile(object $file): int
    {
        $this->fileCount++;
        return 1;
    }
    
    public function visitDirectory(object $directory): int
    {
        $count = 0;
        foreach ($directory->getChildren() as $child) {
            $count += $child->accept($this);
        }
        return $count;
    }
    
    public function getFileCount(): int
    {
        return $this->fileCount;
    }
}
