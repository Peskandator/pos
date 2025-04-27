<?php

namespace App\Presenters\Admin;

use App\Company\Enums\CompanyUserRoles;
use App\Presenters\BaseCompanyPresenter;
use App\Utils\XlsxExporter;
use App\Product\ORM\CategoryRepository;
use App\Product\ORM\DiningTableRepository;
use Nette\Utils\Json;

class ExportPresenter extends BaseCompanyPresenter
{
    public function __construct(
        private readonly XlsxExporter       $xlsxExporter,
        private readonly CategoryRepository $categoryRepository,
        private readonly DiningTableRepository    $tableRepository,
    )
    {
        parent::__construct();
    }

    public function actionProducts(): void
    {
        $products = $this->productRepository->findAll();

        $rows = $this->xlsxExporter->createProductsDataForExport($products);

        $this->xlsxExporter->export($rows, 'Produkty');
        $this->terminate();
    }

    public function actionCategories(): void
    {
        $categories = $this->categoryRepository->findAll();
        $rows = $this->xlsxExporter->createCategoriesDataForExport($categories);

        $this->xlsxExporter->export($rows, 'Kategorie produktů');
        $this->terminate();
    }

    public function actionDiningTables(): void
    {
        $tables = $this->tableRepository->findAll();
        $rows = $this->xlsxExporter->createTablesDataForExport($tables);

        $this->xlsxExporter->export($rows, 'Stoly');
        $this->terminate();
    }

    public function actionOrders
    (
        string $filteredOrdersJson = ''
    ): void
    {
        $permittedRoles = $this->checkPermissionsForUser([CompanyUserRoles::EDTIOR]);

        $orders = $this->orderRepository->findAll();

        if ($filteredOrdersJson !== '') {
            $ordersIds = Json::decode($filteredOrdersJson, Json::FORCE_ARRAY);
            $orders = [];
            foreach ($ordersIds as $orderId) {
                $orders[] = $this->orderRepository->find($orderId);
            }
        }

        $rows = $this->xlsxExporter->createOrdersDataForExport($orders);

        $this->xlsxExporter->export($rows, 'Objednávky');
        $this->terminate();
    }
}
