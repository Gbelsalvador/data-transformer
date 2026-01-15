<?php
// src/Readers/JsonReader.php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Readers;

use Gbelsalvador\DataTransformer\Contracts\ReaderInterface;
use Gbelsalvador\DataTransformer\Exceptions\TransformerException;

class JsonReader implements ReaderInterface
{
    private string $filePath;
    private bool $assoc;

    public function __construct(string $filePath, bool $assoc = true)
    {
        $this->filePath = $filePath;
        $this->assoc = $assoc;
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws TransformerException
     */
    public function read(): array
    {
        if (!file_exists($this->filePath)) {
            throw new TransformerException("JSON file not found: {$this->filePath}");
        }

        $content = file_get_contents($this->filePath);
        if ($content === false) {
            throw new TransformerException("Cannot read JSON file: {$this->filePath}");
        }

        $data = json_decode($content, $this->assoc);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TransformerException("Invalid JSON: " . json_last_error_msg());
        }

        if (!is_array($data)) {
            throw new TransformerException("JSON data must be an array");
        }

        // Ensure all elements are arrays
        return array_map(function ($item) {
            return (array) $item;
        }, $data);
    }
}