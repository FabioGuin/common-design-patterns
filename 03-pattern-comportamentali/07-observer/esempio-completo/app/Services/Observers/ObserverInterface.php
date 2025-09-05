<?php

namespace App\Services\Observers;

interface ObserverInterface
{
    public function update(object $subject): void;
}
