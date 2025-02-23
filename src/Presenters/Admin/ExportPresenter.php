<?php

namespace App\Presenters\Admin;

use Nette;
use App\Utils\XlsxExporter;
use App\Product\ORM\ProductRepository;
use App\Presenters\BaseCompanyPresenter;

class ExportPresenter extends BaseCompanyPresenter
{
    protected XlsxExporter $xlsxExporter;
    protected ProductRepository $productRepository;

    public function __construct(XlsxExporter $xlsxExporter, ProductRepository $productRepository)
    {
        $this->xlsxExporter = $xlsxExporter;
        $this->productRepository = $productRepository;
    }

    public function actionXlsx(): void
    {
        $products = $this->productRepository->findAll();

        // Define columns and headers for the product export
        $columns = [
            'Inv. číslo' => 'getInventoryNumber',
            'Název' => 'getName',
            'Cena' => 'getPrice',
            'DPH' => 'getVatRate',
            'Výrobce' => 'getManufacturer'
        ];

        // Export to XLSX
        $this->xlsxExporter->export($products, $columns, '/tmp/products_export.xlsx');
        $this->terminate();
    }
}

