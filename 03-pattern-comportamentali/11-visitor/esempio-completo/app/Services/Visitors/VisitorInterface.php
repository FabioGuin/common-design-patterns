<?php

namespace App\Services\Visitors;

interface VisitorInterface
{
    public function visitFile(object $file): mixed;
    public function visitDirectory(object $directory): mixed;
}
