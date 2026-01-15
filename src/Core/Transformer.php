<?php
// src/Core/Transformer.php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Core;

use Gbelsalvador\DataTransformer\Contracts\ReaderInterface;
use Gbelsalvador\DataTransformer\Contracts\WriterInterface;

class Transformer
{
    public function transform(ReaderInterface $reader, WriterInterface $writer): void
    {
        $data = $reader->read();
        $writer->write($data);
    }
}