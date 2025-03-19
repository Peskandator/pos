<?php

namespace App\Product\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;

class QrCodeGenerator
{
    public function generate(string $iban, float $amount, string $message): string
    {
        $data = "SPD*1.0*ACC:{$iban}*AM:{$amount}*CC:CZK*MSG:{$message}";

        $qrCode = new QrCode($data);
        $qrCode->setSize(300);
        $qrCode->setMargin(10);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return $result->getDataUri();
    }
}