<?php
// src/Writers/SqlWriter.php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Writers;

use Gbelsalvador\DataTransformer\Contracts\WriterInterface;
use Gbelsalvador\DataTransformer\Exceptions\TransformerException;

class SqlWriter implements WriterInterface
{
    private \PDO $pdo;
    private string $tableName;
    private bool $truncateFirst;

    public function __construct(\PDO $pdo, string $tableName, bool $truncateFirst = false)
    {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
        $this->truncateFirst = $truncateFirst;
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

        try {
            $this->pdo->beginTransaction();

            if ($this->truncateFirst) {
                $this->pdo->exec("TRUNCATE TABLE {$this->tableName}");
            }

            $firstRow = reset($data);
            $columns = array_keys($firstRow);
            $columnsStr = implode(', ', $columns);
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));

            $sql = "INSERT INTO {$this->tableName} ({$columnsStr}) VALUES ({$placeholders})";
            $stmt = $this->pdo->prepare($sql);

            foreach ($data as $row) {
                $values = array_values($row);
                if (!$stmt->execute($values)) {
                    throw new TransformerException("SQL insert failed");
                }
            }

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new TransformerException("SQL write failed: " . $e->getMessage());
        }
    }
}