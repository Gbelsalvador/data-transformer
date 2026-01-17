<?php
// src/Writers/XlsxWriter.php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Writers;

use Gbelsalvador\DataTransformer\Contracts\WriterInterface;
use Gbelsalvador\DataTransformer\Exceptions\TransformerException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriterLib;

class XlsxWriter implements WriterInterface
{
    private string $filePath;
    private ?string $sheetName;

    public function __construct(string $filePath, ?string $sheetName = null)
    {
        $this->filePath = $filePath;
        $this->sheetName = $sheetName;
    }

    /**
     * @param array<int, array<string, mixed>> $data
     * @throws TransformerException
     */
    public function write(array $data): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            
            if ($this->sheetName !== null) {
                $worksheet->setTitle($this->sheetName);
            }

            if (!empty($data)) {
                $firstRow = reset($data);
                
                // Write headers if associative array
                if (isset($firstRow[0]) === false) {
                    $col = 1;
                    foreach (array_keys($firstRow) as $header) {
                        $cell = $worksheet->getCell([$col, 1]);
                        $cell->setValue($header);
                        $col++;
                    }
                    $startRow = 2;
                } else {
                    $startRow = 1;
                }

                // Write data
                $rowNum = $startRow;
                foreach ($data as $row) {
                    $col = 1;
                    foreach ($row as $value) {
                        $cell = $worksheet->getCell([$col, $rowNum]);
                        $cell->setValue($value);
                        $col++;
                    }
                    $rowNum++;
                }
            }

            $writer = new XlsxWriterLib($spreadsheet);
            $writer->save($this->filePath);
        } catch (\Exception $e) {
            throw new TransformerException("Error writing XLSX file: " . $e->getMessage());
        }
    }
}