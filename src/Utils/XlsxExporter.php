<?php

namespace App\Utils;
use App\Entity\Product;
use App\Entity\Category;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class XlsxExporter
{
    public function export(array $data, string $fileObject): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Populate rows with data
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

        // Write the spreadsheet to a file
        $timestamp = date('Y-m-d');
        $filename = '/tmp/' . $fileObject . '_export_' . $timestamp . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);

        // Force download the file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }


    /**
     * Prepares a product data for export
     */
    public function createProductDataForExport(array $products): array
    {
        // Define columns and headers for the product export
        $header = [
            'Inv. číslo',
            'Název',
            'Cena',
            'DPH',
            'Výrobce',
            'Skupina',
            'Kategorie',
            'Kat. číslo'

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
            $row[] = $product->isGroup() ? 'Ano' : 'Ne';

            // Get category details
            $category = $product->getCategory();
            $row[] = $category ? $category->getName() : '';
            $row[] = $category ? $category->getId() : '';

            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Prepares a category data for export
     */
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


    /**
     * Prepares all table data for export
     */
    public function createTableDataForExport(array $tables): array
    {
        $header = [
            'Číslo stolu',
            'Popis'
        ];
        $rows = [];
        $rows[] = $header;

        /**
         * @var table $table
         */
        foreach ($tables as $table) {
            $row = [];
            $row[] = $table->getNumber();
            $row[] = $table->getDescription();
            $rows[] = $row;
        }
        return $rows;
    }

}
