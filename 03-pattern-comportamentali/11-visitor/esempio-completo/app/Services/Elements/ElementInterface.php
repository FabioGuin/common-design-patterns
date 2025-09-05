<?php

namespace App\Services\Elements;

interface ElementInterface
{
    public function accept(object $visitor): mixed;
}
