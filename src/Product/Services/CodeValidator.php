<?php

declare(strict_types=1);

namespace App\Product\Services;

use App\Entity\Category;
use App\Entity\Company;

class CodeValidator
{
    public function __construct()
    {
    }

    public function validateCode(int $code): bool
    {
        if ($code < 1 || $code > 999) {
            return false;
        }

        return true;
    }

    public function isCategoryCodeValid(Company $company, ?int $code, ?int $currentCode = null): string
    {
        if ($code === $currentCode || !$code) {
            return '';
        }

        if (!$this->validateCode($code)) {
            return 'Kód musí být v rozmezí 7-999';
        }

        // TODO: get all categories? to not have duplicate code?
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
}