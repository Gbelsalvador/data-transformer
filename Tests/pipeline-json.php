<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\CsvReader;
use Gbelsalvador\DataTransformer\Writers\JsonWriter;

$result = (new Transformer())
    ->read(new CsvReader(__DIR__ . '/titanic.csv'))
    ->filter(fn (array $row) => ($row['Survived'] ?? null) === '1')
    ->map([
        'passenger_id' => 'PassengerId',
        'full_name' => 'Name',
        'ticket_class' => 'Pclass',
        'sex' => 'Sex',
    ])
    ->validate([
        'passenger_id' => 'required|integer',
        'full_name' => 'required|max:120',
        'ticket_class' => 'required|integer',
        'sex' => 'required|in:male,female',
    ])
    ->write(new JsonWriter(__DIR__ . '/pipeline-output.json'));

printf(
    "Read: %d, Written: %d, Filtered: %d, Invalid: %d\n",
    $result->rowsRead(),
    $result->rowsWritten(),
    $result->rowsFilteredOut(),
    $result->rowsInvalid()
);
