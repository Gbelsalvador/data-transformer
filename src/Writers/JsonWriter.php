<?php
// src/Writers/JsonWriter.php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Writers;

use Gbelsalvador\DataTransformer\Contracts\WriterInterface;
use Gbelsalvador\DataTransformer\Exceptions\TransformerException;

class JsonWriter implements WriterInterface
{
    private string $filePath;
    private int $flags;

    public function __construct(string $filePath, int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    {
        $this->filePath = $filePath;
        $this->flags = $flags;
    }

    /**
     * @param array<int, array<string, mixed>> $data
     * @throws TransformerException
     */
    public function write(array $data): void
    {
        $json = json_encode($data, $this->flags);
        if ($json === false) {
            throw new TransformerException("JSON encoding failed: " . json_last_error_msg());
        }

        $result = file_put_contents($this->filePath, $json);
        if ($result === false) {
            throw new TransformerException("Cannot write JSON file: {$this->filePath}");
        }
    }
}