<?php
// src/Readers/CsvReader.php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Readers;

use Gbelsalvador\DataTransformer\Contracts\ReaderInterface;
use Gbelsalvador\DataTransformer\Exceptions\TransformerException;

class CsvReader implements ReaderInterface
{
    private string $filePath;
    private string $delimiter;
    private bool $hasHeader;
    private string $enclosure;
    private string $escape;

    public function __construct(
        string $filePath,
        string $delimiter = ',',
        bool $hasHeader = true,
        string $enclosure = '"',
        string $escape = '\\'
    ) {
        $this->filePath = $filePath;
        $this->delimiter = $delimiter;
        $this->hasHeader = $hasHeader;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws TransformerException
     */
    public function read(): array
    {
        if (!file_exists($this->filePath)) {
            throw new TransformerException("CSV file not found: {$this->filePath}");
        }

        $handle = fopen($this->filePath, 'r');
        if ($handle === false) {
            throw new TransformerException("Cannot open CSV file: {$this->filePath}");
        }

        $data = [];
        $headers = [];

        if ($this->hasHeader) {
            $headerRow = fgetcsv($handle, 0, $this->delimiter, $this->enclosure, $this->escape);
            if ($headerRow !== false) {
                $headers = $headerRow;
            }
        }

        while (($row = fgetcsv($handle, 0, $this->delimiter, $this->enclosure, $this->escape)) !== false) {
            if ($this->hasHeader && !empty($headers)) {
                $data[] = array_combine($headers, $row);
            } else {
                $data[] = $row;
            }
        }

        fclose($handle);
        return $data;
    }
}