<?php

namespace App\Services\Subjects;

interface SubjectInterface
{
    public function attach(object $observer): void;
    public function detach(object $observer): void;
    public function notify(): void;
}
