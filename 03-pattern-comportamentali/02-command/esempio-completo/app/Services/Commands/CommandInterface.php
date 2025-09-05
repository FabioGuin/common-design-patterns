<?php

namespace App\Services\Commands;

interface CommandInterface
{
    public function execute(): void;
    public function undo(): void;
    public function getDescription(): string;
}
