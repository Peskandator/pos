<?php

namespace App\Utils;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class XlsxExporter
{
    public function export(array $data, array $columns, string $filename): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set column headers
        $columnIndex = 1;
        foreach ($columns as $header => $method) {
            // The coordinate is a string like 'A1', 'B1', 'C1', etc.
            $cellCoordinate = Coordinate::stringFromColumnIndex($columnIndex) . '1';
            $sheet->setCellValue($cellCoordinate, $header); // Pass the correct coordinate format
            $columnIndex++;
        }

        // Populate rows with data
        $rowIndex = 2;
        foreach ($data as $row) {
            $columnIndex = 1;

            foreach ($columns as $header => $method) {
                $cellCoordinate = Coordinate::stringFromColumnIndex($columnIndex) . $rowIndex;
                // Use reflection or any method to access data dynamically

                // TODO vypsat data
                $sheet->setCellValue($cellCoordinate, $row->$method()); // Pass the correct coordinate and value
                $columnIndex++;
            }
            $rowIndex++;
        }

        // Write the spreadsheet to a file
        // TODO: filename
        $filename = '/tmp/products_export.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);

        // Force download the file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }
}
