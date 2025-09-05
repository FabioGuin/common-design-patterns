<?php

namespace App\Services\Observers;

class EmailObserver implements ObserverInterface
{
    public function update(object $subject): void
    {
        if (method_exists($subject, 'getStatus')) {
            echo "Sending email notification for status: " . $subject->getStatus() . "\n";
        }
    }
}
