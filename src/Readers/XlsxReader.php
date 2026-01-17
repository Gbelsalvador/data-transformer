<?php
// src/Readers/XlsxReader.php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Readers;

use Gbelsalvador\DataTransformer\Contracts\ReaderInterface;
use Gbelsalvador\DataTransformer\Exceptions\TransformerException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as PhpSpreadsheetDate;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class XlsxReader implements ReaderInterface
{
    private string $filePath;
    private ?string $sheetName;
    private bool $hasHeader;

    public function __construct(string $filePath, ?string $sheetName = null, bool $hasHeader = true)
    {
        $this->filePath = $filePath;
        $this->sheetName = $sheetName;
        $this->hasHeader = $hasHeader;
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws TransformerException
     */
    public function read(): array
    {
        if (!file_exists($this->filePath)) {
            throw new TransformerException("XLSX file not found: {$this->filePath}");
        }

        try {
            $spreadsheet = IOFactory::load($this->filePath);
            $worksheet = $this->sheetName !== null 
                ? $spreadsheet->getSheetByName($this->sheetName)
                : $spreadsheet->getActiveSheet();

            if (!$worksheet instanceof Worksheet) {
                throw new TransformerException("Sheet not found: {$this->sheetName}");
            }

            return $this->worksheetToArray($worksheet);
        } catch (\Exception $e) {
            throw new TransformerException("Error reading XLSX file: " . $e->getMessage());
        }
    }

    private function worksheetToArray(Worksheet $worksheet): array
    {
        $data = [];
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $headers = [];
        $startRow = 1;

        if ($this->hasHeader) {
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $cell = $worksheet->getCell([$col, 1]);
                $headers[] = $cell->getValue() ?: "Column{$col}";
            }
            $startRow = 2;
        }

        for ($row = $startRow; $row <= $highestRow; $row++) {
            $rowData = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $cell = $worksheet->getCell([$col, $row]);
                $value = $this->normalizeCellValue($cell);
                
                if ($this->hasHeader && !empty($headers)) {
                    $rowData[$headers[$col - 1]] = $value;
                } else {
                    $rowData[] = $value;
                }
            }
            $data[] = $rowData;
        }

        return $data;
    }

    private function normalizeCellValue(Cell $cell): mixed
    {
        $value = $cell->getValue();

        if ($value instanceof RichText) {
            $value = $value->getPlainText();
        }

        
        if (PhpSpreadsheetDate::isDateTime($cell)) {
            try {
                $dt = PhpSpreadsheetDate::excelToDateTimeObject((float) $value);
                return $dt->format('c');
            } catch (\Throwable $e) {
                
            }
        }

        if (is_numeric($value)) {
            
            if ((string) (int) $value === (string) $value) {
                return (int) $value;
            }
            return (float) $value;
        }

        if (is_null($value) || is_bool($value)) {
            return $value;
        }

        return (string) $value;
    }
}