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
    private array $filters;

    public function __construct(
        \PDO $pdo,
        string $tableName,
        array $columns = ['*'],
        ?string $whereClause = null,
        array $whereParams = [],
        array $filters = []
    ) {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
        $this->columns = $columns;
        $this->whereClause = $whereClause;
        $this->whereParams = $whereParams;
        $this->filters = $filters;
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws TransformerException
     */
    public function read(): array
    {
        $tableName = $this->quoteIdentifier($this->tableName);
        $columns = $this->buildColumnList($this->columns);
        $sql = "SELECT {$columns} FROM {$tableName}";
        $params = $this->whereParams;

        if ($this->whereClause !== null && $this->filters !== []) {
            throw new TransformerException('Use either whereClause or filters, not both.');
        }

        if ($this->filters !== []) {
            [$filterSql, $params] = $this->buildWhereFromFilters($this->filters);
            $sql .= " WHERE {$filterSql}";
        } elseif ($this->whereClause !== null) {
            $sql .= " WHERE " . $this->validateWhereClause($this->whereClause);
        }

        $stmt = $this->pdo->prepare($sql);
        if (!$stmt->execute($params)) {
            throw new TransformerException("SQL query failed: " . implode(', ', $stmt->errorInfo()));
        }

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $result ?: [];
    }

    /**
     * @param array<int, string> $columns
     */
    private function buildColumnList(array $columns): string
    {
        if ($columns === ['*']) {
            return '*';
        }

        $quotedColumns = [];
        foreach ($columns as $column) {
            $quotedColumns[] = $this->quoteIdentifier($column);
        }

        return implode(', ', $quotedColumns);
    }

    private function quoteIdentifier(string $identifier): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new TransformerException("Invalid SQL identifier: {$identifier}");
        }

        return $identifier;
    }

    private function validateWhereClause(string $whereClause): string
    {
        if (preg_match('/(;|--|\/\*|\*\/|["\'`])/', $whereClause) === 1) {
            throw new TransformerException('Unsafe SQL WHERE clause detected.');
        }

        return $whereClause;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function buildWhereFromFilters(array $filters): array
    {
        $clauses = [];
        $params = [];
        $index = 0;

        foreach ($filters as $column => $definition) {
            $quotedColumn = $this->quoteIdentifier($column);
            $paramName = ':w_' . $index;

            if (!is_array($definition)) {
                $clauses[] = "{$quotedColumn} = {$paramName}";
                $params[$paramName] = $definition;
                $index++;
                continue;
            }

            $operator = strtolower((string) ($definition['operator'] ?? '='));
            $value = $definition['value'] ?? null;

            switch ($operator) {
                case '=':
                case '!=':
                case '>':
                case '>=':
                case '<':
                case '<=':
                case 'like':
                    $clauses[] = "{$quotedColumn} {$operator} {$paramName}";
                    $params[$paramName] = $value;
                    $index++;
                    break;

                case 'in':
                    if (!is_array($value) || $value === []) {
                        throw new TransformerException("Filter 'in' requires a non-empty array for column {$column}.");
                    }

                    $placeholders = [];
                    foreach ($value as $inValue) {
                        $inParamName = ':w_' . $index;
                        $placeholders[] = $inParamName;
                        $params[$inParamName] = $inValue;
                        $index++;
                    }
                    $clauses[] = "{$quotedColumn} IN (" . implode(', ', $placeholders) . ")";
                    break;

                case 'is_null':
                    $clauses[] = "{$quotedColumn} IS NULL";
                    break;

                case 'is_not_null':
                    $clauses[] = "{$quotedColumn} IS NOT NULL";
                    break;

                default:
                    throw new TransformerException("Unsupported filter operator '{$operator}' for column {$column}.");
            }
        }

        if ($clauses === []) {
            throw new TransformerException('Filters cannot be empty when provided.');
        }

        return [implode(' AND ', $clauses), $params];
    }
}
