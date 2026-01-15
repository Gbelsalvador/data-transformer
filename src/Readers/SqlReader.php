<?php
// src/Readers/SqlReader.php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Readers;

use Gbelsalvador\DataTransformer\Contracts\ReaderInterface;
use Gbelsalvador\DataTransformer\Exceptions\TransformerException;

class SqlReader implements ReaderInterface
{
    private \PDO $pdo;
    private string $tableName;
    private array $columns;
    private ?string $whereClause;
    private array $whereParams;

    public function __construct(
        \PDO $pdo,
        string $tableName,
        array $columns = ['*'],
        ?string $whereClause = null,
        array $whereParams = []
    ) {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
        $this->columns = $columns;
        $this->whereClause = $whereClause;
        $this->whereParams = $whereParams;
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws TransformerException
     */
    public function read(): array
    {
        $columns = implode(', ', $this->columns);
        $sql = "SELECT {$columns} FROM {$this->tableName}";

        if ($this->whereClause !== null) {
            $sql .= " WHERE {$this->whereClause}";
        }

        $stmt = $this->pdo->prepare($sql);
        if (!$stmt->execute($this->whereParams)) {
            throw new TransformerException("SQL query failed: " . implode(', ', $stmt->errorInfo()));
        }

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $result ?: [];
    }
}