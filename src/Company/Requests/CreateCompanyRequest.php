<?php

namespace App\Company\Requests;

class CreateCompanyRequest
{
    public function __construct(
        public string $name,
        public string $companyId,
        public string $country,
        public string $city,
        public string $zipCode,
        public string $street,
    ) {
    }
}
