<?php

namespace App\Utils;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class XlsxExporter
{
    public function export(array $data, string $filename): void
    {
        bdump($data);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Populate rows with data
        $rowIndex = 1;
        foreach ($data as $row) {
            $columnIndex = 1;

            foreach ($row as $column) {
                $cellCoordinate = Coordinate::stringFromColumnIndex($columnIndex) . $rowIndex;
                $sheet->setCellValue($cellCoordinate, $column); // Pass the correct coordinate and value
                $columnIndex++;
            }
            $rowIndex++;
        }

        // Write the spreadsheet to a file
        $timestamp = date('Y-m-d');
        $filename = '/tmp/produkty_export_' . $timestamp . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);

        // Force download the file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }
}
