<?php

declare(strict_types=1);

namespace App\Utils;

class FloatFilter
{
    public function __invoke(?float $number): string
    {
        return str_replace('.', ',', strval($number));
    }
}
