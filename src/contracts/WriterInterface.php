<?php
// src/Contracts/WriterInterface.php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Contracts;

interface WriterInterface
{
    /**
     * @param array<int, array<string, mixed>> $data
     */
    public function write(array $data): void;
}