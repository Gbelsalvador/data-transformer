<?php
// src/Contracts/ReaderInterface.php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Contracts;

interface ReaderInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function read(): array;
}