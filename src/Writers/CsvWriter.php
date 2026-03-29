<?php
// src/Writers/CsvWriter.php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Writers;

use Gbelsalvador\DataTransformer\Contracts\WriterInterface;
use Gbelsalvador\DataTransformer\Exceptions\TransformerException;

class CsvWriter implements WriterInterface
{
    private string $filePath;
    private string $delimiter;
    private string $enclosure;
    private bool $includeHeader;

    public function __construct(
        string $filePath,
        string $delimiter = ',',
        string $enclosure = '"',
        bool $includeHeader = true
    ) {
        $this->filePath = $filePath;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->includeHeader = $includeHeader;
    }

    /**
     * @param array<int, array<string, mixed>> $data
     * @throws TransformerException
     */
    public function write(array $data): void
    {
        if (empty($data)) {
            return;
        }

        $handle = fopen($this->filePath, 'w');
        if ($handle === false) {
            throw new TransformerException("Cannot open CSV file for writing: {$this->filePath}");
        }

        $firstRow = reset($data);
        
        if ($this->includeHeader && isset($firstRow[0]) === false) {
            $headers = array_map([$this, 'sanitizeSpreadsheetValue'], array_keys($firstRow));
            fputcsv($handle, $headers, $this->delimiter, $this->enclosure);
        }

        foreach ($data as $row) {
            $sanitizedRow = array_map([$this, 'sanitizeSpreadsheetValue'], $row);
            fputcsv($handle, $sanitizedRow, $this->delimiter, $this->enclosure);
        }

        fclose($handle);
    }

    private function sanitizeSpreadsheetValue(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        if (preg_match('/^[=\-+@]/', $value) === 1) {
            return "'" . $value;
        }

        return $value;
    }
}
