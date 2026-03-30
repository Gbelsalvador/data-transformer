<?php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Tests;

use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\CsvReader;
use Gbelsalvador\DataTransformer\Readers\JsonReader;
use Gbelsalvador\DataTransformer\Readers\XmlReader;
use Gbelsalvador\DataTransformer\Writers\CsvWriter;
use Gbelsalvador\DataTransformer\Writers\JsonWriter;
use Gbelsalvador\DataTransformer\Writers\XmlWriter;
use PHPUnit\Framework\TestCase;

final class FileIntegrationTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'data-transformer-tests-' . uniqid('', true);
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tempDir . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }
    }

    public function testCsvToJsonRoundTripWorksThroughTransformer(): void
    {
        $csvPath = $this->tempPath('users.csv');
        $jsonPath = $this->tempPath('users.json');

        file_put_contents($csvPath, "id,name,email\n1,Ada,ada@example.com\n2,Grace,grace@example.com\n");

        $result = (new Transformer())->transform(
            new CsvReader($csvPath),
            new JsonWriter($jsonPath)
        );

        $decoded = (new JsonReader($jsonPath))->read();

        self::assertSame(2, $result->rowsRead());
        self::assertSame(2, $result->rowsWritten());
        self::assertSame([
            ['id' => '1', 'name' => 'Ada', 'email' => 'ada@example.com'],
            ['id' => '2', 'name' => 'Grace', 'email' => 'grace@example.com'],
        ], $decoded);
    }

    public function testXmlWriterAndReaderRoundTripSimpleRows(): void
    {
        $xmlPath = $this->tempPath('users.xml');
        $data = [
            ['name' => 'Ada', 'email' => 'ada@example.com'],
            ['name' => 'Grace', 'email' => 'grace@example.com'],
        ];

        (new XmlWriter($xmlPath, 'users', 'user'))->write($data);

        $readBack = (new XmlReader($xmlPath))->read();

        self::assertSame([
            [
                'name' => ['_value' => 'Ada'],
                'email' => ['_value' => 'ada@example.com'],
            ],
            [
                'name' => ['_value' => 'Grace'],
                'email' => ['_value' => 'grace@example.com'],
            ],
        ], $readBack);
    }

    public function testCsvWriterSanitizesSpreadsheetFormulas(): void
    {
        $csvPath = $this->tempPath('export.csv');

        (new CsvWriter($csvPath))->write([
            ['name' => 'Ada', 'note' => '=CMD()'],
        ]);

        $contents = file_get_contents($csvPath);

        self::assertIsString($contents);
        self::assertStringContainsString("'=CMD()", $contents);
    }

    private function tempPath(string $filename): string
    {
        return $this->tempDir . DIRECTORY_SEPARATOR . $filename;
    }
}
