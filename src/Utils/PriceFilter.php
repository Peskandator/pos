<?php

declare(strict_types=1);

namespace App\Utils;

class PriceFilter
{
    public function __invoke(?float $number): string
    {
        if ($number !== null) {
            if (floor($number) === $number) {
                $formattedNumber = number_format($number, 0, ',', ' ');
            } else {
                $formattedNumber = number_format($number, 2, ',', ' ');
            }

            return $formattedNumber . ' Kč';
        }
        return '';
    }
}
