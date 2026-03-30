<?php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Tests;

use Gbelsalvador\DataTransformer\Contracts\ReaderInterface;
use Gbelsalvador\DataTransformer\Contracts\WriterInterface;
use Gbelsalvador\DataTransformer\Core\ExecutionResult;
use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Exceptions\TransformerException;
use Gbelsalvador\DataTransformer\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

final class TransformerPipelineTest extends TestCase
{
    public function testTransformWritesRowsAndReturnsExecutionResult(): void
    {
        $reader = new ArrayReader([
            ['id' => 1, 'email' => 'ada@example.com'],
            ['id' => 2, 'email' => 'grace@example.com'],
        ]);
        $writer = new ArrayWriter();

        $result = (new Transformer())->transform($reader, $writer);

        self::assertInstanceOf(ExecutionResult::class, $result);
        self::assertSame(2, $result->rowsRead());
        self::assertSame(2, $result->rowsWritten());
        self::assertSame($reader->read(), $writer->writtenData());
    }

    public function testPipelineFiltersMapsValidatesAndReportsErrors(): void
    {
        $writer = new ArrayWriter();

        $result = (new Transformer())
            ->read(new ArrayReader([
                ['id' => '1', 'first_name' => 'Ada', 'last_name' => 'Lovelace', 'email' => 'ada@example.com', 'active' => '1'],
                ['id' => '2', 'first_name' => 'Bad', 'last_name' => 'Email', 'email' => 'not-an-email', 'active' => '1'],
                ['id' => '3', 'first_name' => 'Hidden', 'last_name' => 'User', 'email' => 'hidden@example.com', 'active' => '0'],
            ]))
            ->filter(fn (array $row) => $row['active'] === '1')
            ->map([
                'id' => 'id',
                'full_name' => fn (array $row) => $row['first_name'] . ' ' . $row['last_name'],
                'email' => 'email',
            ])
            ->validate([
                'id' => 'required|integer',
                'full_name' => 'required|max:50',
                'email' => 'required|email',
            ])
            ->write($writer);

        self::assertSame(3, $result->rowsRead());
        self::assertSame(1, $result->rowsFilteredOut());
        self::assertSame(1, $result->rowsInvalid());
        self::assertSame(1, $result->rowsWritten());
        self::assertCount(1, $result->validationErrors());
        self::assertSame([
            [
                'id' => '1',
                'full_name' => 'Ada Lovelace',
                'email' => 'ada@example.com',
            ],
        ], $writer->writtenData());
    }

    public function testMapCallableMustReturnArray(): void
    {
        $this->expectException(TransformerException::class);
        $this->expectExceptionMessage('The map callable must return an array row.');

        (new Transformer())
            ->read(new ArrayReader([['id' => 1]]))
            ->map(fn () => 'invalid');
    }

    public function testUnsupportedValidationRuleThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        (new Transformer())
            ->read(new ArrayReader([['name' => 'Ada']]))
            ->validate([
                'name' => 'unknown_rule',
            ]);
    }

    public function testFilterCannotRunBeforeRead(): void
    {
        $this->expectException(TransformerException::class);
        $this->expectExceptionMessage('No data loaded. Call read() or transform() first.');

        (new Transformer())->filter(fn () => true);
    }
}

final class ArrayReader implements ReaderInterface
{
    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function __construct(private array $rows)
    {
    }

    public function read(): array
    {
        return $this->rows;
    }
}

final class ArrayWriter implements WriterInterface
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $data = [];

    public function write(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function writtenData(): array
    {
        return $this->data;
    }
}
