<?php

namespace App\Presenters\Admin;

use App\Presenters\BaseCompanyPresenter;
use App\Utils\XlsxExporter;
use App\Product\ORM\CategoryRepository;

class ExportPresenter extends BaseCompanyPresenter
{
    public function __construct(
        private readonly XlsxExporter       $xlsxExporter,
        private readonly CategoryRepository $categoryRepository
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
}



