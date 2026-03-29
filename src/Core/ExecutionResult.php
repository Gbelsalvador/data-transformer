<?php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Core;

class ExecutionResult
{
    /**
     * @param array<int, array<string, mixed>> $validationErrors
     */
    public function __construct(
        private int $rowsRead,
        private int $rowsWritten,
        private int $rowsFilteredOut,
        private int $rowsInvalid,
        private float $durationSeconds,
        private array $validationErrors = []
    ) {
    }

    public function rowsRead(): int
    {
        return $this->rowsRead;
    }

    public function rowsWritten(): int
    {
        return $this->rowsWritten;
    }

    public function rowsFilteredOut(): int
    {
        return $this->rowsFilteredOut;
    }

    public function rowsInvalid(): int
    {
        return $this->rowsInvalid;
    }

    public function errorCount(): int
    {
        return count($this->validationErrors);
    }

    public function duration(): float
    {
        return $this->durationSeconds;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function validationErrors(): array
    {
        return $this->validationErrors;
    }
}
