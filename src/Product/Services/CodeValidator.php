<?php

declare(strict_types=1);

namespace App\Product\Services;

use App\Entity\Category;
use App\Entity\Company;
use App\Entity\DiningTable;

class CodeValidator
{
    public function __construct()
    {
    }

    public function validateCode(int $code): bool
    {
        return !($code < 1 || $code > 999);
    }

    public function isCategoryCodeValid(Company $company, ?int $code, ?int $currentCode = null): string
    {
        if ($code === $currentCode || !$code) {
            return '';
        }

        if (!$this->validateCode($code)) {
            return 'Kód musí být v rozmezí 7-999';
        }

        $categories = $company->getCategories();
        /**
         * @var Category $category
         */
        foreach ($categories as $category) {
            if ($code === $category->getCode()) {
                return 'Zadaný kód je již obsazen';
            }
        }

        return '';
    }

    public function isDiningTableNumberValid(Company $company, ?int $number, ?int $currentNumber = null): string
    {
        if ($number === $currentNumber || !$number) {
            return '';
        }

        if (!$this->validateCode($number)) {
            return 'Kód musí být v rozmezí 1-999';
        }

        $tables = $company->getDiningTables();
        /**
         * @var DiningTable $table
         */
        foreach ($tables as $table) {
            if ($number === $table->getNumber()) {
                return 'Zadané číslo stolu je již obsazeno';
            }
        }

        return '';
    }
}