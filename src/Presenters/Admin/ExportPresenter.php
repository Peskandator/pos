<?php

namespace App\Presenters\Admin;

use App\Company\Enums\CompanyUserRoles;
use App\Presenters\BaseCompanyPresenter;
use App\Utils\XlsxExporter;
use Nette\Utils\Json;

class ExportPresenter extends BaseCompanyPresenter
{
    public function __construct(
        private readonly XlsxExporter       $xlsxExporter,
    )
    {
        parent::__construct();
    }

    public function actionProducts(): void
    {
        $products = $this->currentCompany->getProducts();

        $rows = $this->xlsxExporter->createProductsDataForExport($products);

        $this->xlsxExporter->export($rows, 'Produkty');
        $this->terminate();
    }

    public function actionCategories(): void
    {
        $categories = $this->currentCompany->getCategories();
        $rows = $this->xlsxExporter->createCategoriesDataForExport($categories);

        $this->xlsxExporter->export($rows, 'Kategorie produktů');
        $this->terminate();
    }

    public function actionDiningTables(): void
    {
        $tables = $this->currentCompany->getDiningTables();
        $rows = $this->xlsxExporter->createTablesDataForExport($tables);

        $this->xlsxExporter->export($rows, 'Stoly');
        $this->terminate();
    }

    public function actionOrders(): void
    {
        $permittedRoles = $this->checkPermissionsForUser([CompanyUserRoles::EDTIOR]);
        $orders = $this->currentCompany->getOrders()->toArray();
        $rows = $this->xlsxExporter->createOrdersDataForExport($orders);

        $this->xlsxExporter->export($rows, 'Objednávky');
        $this->terminate();
    }

    public function actionFilteredOrders(string $filteredOrdersJson, string $filename = "Objednávky"): void
    {
        $permittedRoles = $this->checkPermissionsForUser([CompanyUserRoles::ADMIN]);

        if ($filteredOrdersJson !== '') {
            $ordersIds = Json::decode($filteredOrdersJson, Json::FORCE_ARRAY);
            $orders = [];
            foreach ($ordersIds as $orderId) {
                $orders[] = $this->orderRepository->find($orderId);
            }
        }

        $rows = $this->xlsxExporter->createOrdersDataForExport($orders);

        $this->xlsxExporter->export($rows, $filename);
        $this->terminate();
    }
}
