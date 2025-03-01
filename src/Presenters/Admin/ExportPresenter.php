<?php

namespace App\Presenters\Admin;

use App\Presenters\BaseCompanyPresenter;
use App\Utils\XlsxExporter;
use App\Product\ORM\CategoryRepository;
use App\Product\ORM\TableRepository;

class ExportPresenter extends BaseCompanyPresenter
{
    public function __construct(
        private readonly XlsxExporter       $xlsxExporter,
        private readonly CategoryRepository $categoryRepository,
        private readonly TableRepository    $tableRepository
    )
    {
        parent::__construct();
    }

    public function actionProduct(): void
    {
        $products = $this->productRepository->findAll();

        $rows = $this->xlsxExporter->createProductDataForExport($products);

        $this->xlsxExporter->export($rows, 'produkty');
        $this->terminate();
    }

    public function actionCategory(): void
    {
        $category = $this->categoryRepository->findAll();
        $rows = $this->xlsxExporter->createCategoryDataForExport($category);

        $this->xlsxExporter->export($rows, 'kategorie');
        $this->terminate();
    }

    public function actionDiningTable(): void
    {
        $table = $this->tableRepository->findAll();
        $rows = $this->xlsxExporter->createTableDataForExport($table);

        $this->xlsxExporter->export($rows, 'stoly');
        $this->terminate();
    }
}



