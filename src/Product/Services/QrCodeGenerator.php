<?php

namespace App\Product\Services;

use Defr\QRPlatba\QRPlatba;

class QrCodeGenerator
{
    public function generate(string $iban, float $amount, string $message): string
    {
        $amount = (float) $amount;

        $qrPlatba = new QRPlatba();
        $qrPlatba->setIBAN($iban)
            ->setAmount($amount)
            ->setCurrency('CZK')
            ->setMessage($message);

        return $qrPlatba->getDataUri();
    }
}
