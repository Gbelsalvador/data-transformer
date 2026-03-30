<?php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Tests;

use Gbelsalvador\DataTransformer\Exceptions\TransformerException;
use Gbelsalvador\DataTransformer\Readers\SqlReader;
use Gbelsalvador\DataTransformer\Writers\SqlWriter;
use PDO;
use PHPUnit\Framework\TestCase;

final class SqlReadWriteTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('The pdo_sqlite driver is required for SQL integration tests.');
        }

        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec(
            'CREATE TABLE users (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                email TEXT,
                active INTEGER NOT NULL,
                created_at TEXT,
                notes TEXT
            )'
        );
        $this->pdo->exec(
            "INSERT INTO users (id, name, email, active, created_at, notes) VALUES
            (1, 'Ada', 'ada@example.com', 1, '2026-01-10', NULL),
            (2, 'Grace', 'grace@example.com', 1, '2026-02-15', 'engineer'),
            (3, 'Linus', 'linus@example.com', 0, '2025-12-31', NULL)"
        );
    }

    public function testSqlReaderSupportsStructuredFilters(): void
    {
        $reader = new SqlReader(
            pdo: $this->pdo,
            tableName: 'users',
            columns: ['id', 'name'],
            filters: [
                'active' => 1,
                'created_at' => [
                    'operator' => '>=',
                    'value' => '2026-01-01',
                ],
            ]
        );

        self::assertSame([
            ['id' => 1, 'name' => 'Ada'],
            ['id' => 2, 'name' => 'Grace'],
        ], $reader->read());
    }

    public function testSqlReaderSupportsInAndIsNullFilters(): void
    {
        $reader = new SqlReader(
            pdo: $this->pdo,
            tableName: 'users',
            columns: ['id', 'name'],
            filters: [
                'id' => [
                    'operator' => 'in',
                    'value' => [1, 3],
                ],
                'notes' => [
                    'operator' => 'is_null',
                ],
            ]
        );

        self::assertSame([
            ['id' => 1, 'name' => 'Ada'],
            ['id' => 3, 'name' => 'Linus'],
        ], $reader->read());
    }

    public function testSqlReaderRejectsMixedWhereClauseAndFilters(): void
    {
        $this->expectException(TransformerException::class);
        $this->expectExceptionMessage('Use either whereClause or filters, not both.');

        (new SqlReader(
            pdo: $this->pdo,
            tableName: 'users',
            whereClause: 'active = :active',
            whereParams: [':active' => 1],
            filters: ['active' => 1]
        ))->read();
    }

    public function testSqlReaderRejectsUnsafeIdentifier(): void
    {
        $this->expectException(TransformerException::class);
        $this->expectExceptionMessage('Invalid SQL identifier');

        (new SqlReader(
            pdo: $this->pdo,
            tableName: 'users; DROP TABLE users',
        ))->read();
    }

    public function testSqlWriterInsertsRowsIntoDatabase(): void
    {
        $this->pdo->exec('CREATE TABLE imports (id INTEGER, label TEXT, active INTEGER)');

        $writer = new SqlWriter($this->pdo, 'imports');
        $writer->write([
            ['id' => 10, 'label' => 'First', 'active' => 1],
            ['id' => 11, 'label' => 'Second', 'active' => 0],
        ]);

        $rows = $this->pdo
            ->query('SELECT id, label, active FROM imports ORDER BY id')
            ->fetchAll(PDO::FETCH_ASSOC);

        self::assertSame([
            ['id' => 10, 'label' => 'First', 'active' => 1],
            ['id' => 11, 'label' => 'Second', 'active' => 0],
        ], $rows);
    }

    public function testSqlWriterRejectsUnsafeColumnIdentifier(): void
    {
        $this->pdo->exec('CREATE TABLE imports (id INTEGER, label TEXT)');

        $this->expectException(TransformerException::class);
        $this->expectExceptionMessage('Invalid SQL identifier');

        (new SqlWriter($this->pdo, 'imports'))->write([
            ['id' => 1, 'label); DROP TABLE imports; --' => 'boom'],
        ]);
    }
}
