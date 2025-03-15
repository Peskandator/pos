<?php

namespace App\Utils;
use App\Entity\DiningTable;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\ProductInGroup;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class XlsxExporter
{
    public function export(array $data, string $fileName): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $rowIndex = 1;
        foreach ($data as $row) {
            $columnIndex = 1;

            foreach ($row as $column) {
                $cellCoordinate = Coordinate::stringFromColumnIndex($columnIndex) . $rowIndex;
                $sheet->setCellValue($cellCoordinate, $column);
                $columnIndex++;
            }
            $rowIndex++;
        }

        $timestamp = date('Y-m-d');
        $filePath = '/tmp/' . $fileName . '_export_' . $timestamp . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $downloadFileName = $fileName . ' ' . $timestamp . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $downloadFileName . '"');
        header('Cache-Control: max-age=0');
        readfile($filePath);
    }


    public function createProductDataForExport(array $products): array
    {
        $header = [
            'Inv. číslo',
            'Název',
            'Cena',
            'DPH',
            'Cena bez DPH',
            'Výrobce',
            'Kategorie',
            'Skupina',
            'Produkty ve skupině'
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
            $row[] = $product->getPrice() . ' Kč';
            $row[] = $product->getVatRatePercentage();
            $row[] = $product->getPriceWithoutVatRate();
            $row[] = $product->getManufacturer();

            $category = $product->getCategory();
            $row[] = $category ? $category->getCategoryCodeAndName() : '';

            $row[] = $product->isGroup() ? 'Ano' : 'Ne';
            $row[] = $this->getGroupedProductsText($product);

            $rows[] = $row;
        }
        return $rows;
    }

    public function createCategoryDataForExport(array $categories): array
    {
        $header = [
            'Kód',
            'Kategorie'
        ];
        $rows = [];
        $rows[] = $header;

        /**
         * @var Category $category
         */
        foreach ($categories as $category) {
            $row = [];
            $row[] = $category->getCode();
            $row[] = $category->getName();
            $rows[] = $row;
        }
        return $rows;
    }


    public function createTableDataForExport(array $tables): array
    {
        $header = [
            'Číslo stolu',
            'Popis'
        ];
        $rows = [];
        $rows[] = $header;

        /**
         * @var DiningTable $table
         */
        foreach ($tables as $table) {
            $row = [];
            $row[] = $table->getNumber();
            $row[] = $table->getDescription();
            $rows[] = $row;
        }
        return $rows;
    }

    private function getGroupedProductsText(Product $product): string
    {
        $groupedProductsText = '';
        if ($product->isGroup()) {
            $productsInGroup = $product->getProductsInGroup()->toArray();

            $productsCount = count($productsInGroup);

            $counter = 0;
            /** @var ProductInGroup $productInGroup */
            foreach ($productsInGroup as $productInGroup) {
                $counter++;
                $groupedProductsText .= $productInGroup->getProduct()->getName();
                if ($counter !== $productsCount) {
                    $groupedProductsText .= ', ';
                }
            }
        }

        return $groupedProductsText;
    }
}
