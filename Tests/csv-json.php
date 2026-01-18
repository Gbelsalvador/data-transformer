<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\CsvReader;
use Gbelsalvador\DataTransformer\Writers\JsonWriter;

$transformer = new Transformer();
$reader = new CsvReader(__DIR__ . '/titanic.csv');
$writer = new JsonWriter(__DIR__ . '/output.json');

$transformer->transform($reader, $writer);