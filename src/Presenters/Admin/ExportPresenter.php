<?php

namespace App\Presenters\Admin;

use App\Entity\Product;
use App\Presenters\BaseCompanyPresenter;
use App\Utils\XlsxExporter;

class ExportPresenter extends BaseCompanyPresenter
{
    public function __construct(
        private readonly XlsxExporter $xlsxExporter,
    )
    {
        parent::__construct();
    }

    public function actionProduct(): void
    {
        $products = $this->productRepository->findAll();

        $rows = $this->createProductDataForExport($products);

        $columns = [];
        // Export to XLSX
        $this->xlsxExporter->export($products, $columns, '/tmp/products_export.xlsx');
        $this->terminate();
    }

    private function createProductDataForExport(array $products): array
    {
        // Define columns and headers for the product export
        $header = [
            'Inv. číslo',
            'Název',
            'Cena',
            'DPH',
            'Výrobce'
        ];

        $rows = [];
        $rows[] = $header;

        /**
         * @var Product $product
         */
        foreach ($products as $product) {
            $row = [];

            $row[] = $product->getInventoryNumber();
            $row[] = $product->getName();
            $row[] = $product->getPrice();
            $row[] = $product->getVatRate();
            $row[] = $product->getManufacturer();

            // TODO add more columns
        }

        return $rows;
    }
}

