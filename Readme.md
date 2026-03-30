# Data Transformer

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.0-777BB4.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-PHPUnit-success.svg)](phpunit.xml.dist)
[![Standards](https://img.shields.io/badge/style-PSR--12-brightgreen.svg)](https://www.php-fig.org/psr/psr-12/)

`gbelsalvador/data-transformer` is a PHP library for reading, transforming, validating, and exporting structured data across multiple formats.

It is designed for practical data workflows:

- import from `CSV`, `JSON`, `XML`, `SQL`, or `XLSX`
- normalize data into a consistent PHP array structure
- filter, map, and validate rows through a fluent pipeline
- export the result to another format

The project aims to offer a clean developer experience, predictable behavior, and a solid base for production-oriented data conversion workflows.

## Documentation

- French README: [`README.fr.md`](README.fr.md)
- HTML documentation: [`docs/index.html`](docs/index.html)

## Why This Library

Most conversion utilities stop at file-to-file export. Data Transformer goes further by adding a transformation pipeline between the reader and writer.

That means you can:

- convert formats
- rename and reshape fields
- filter rows
- validate data before export
- get an execution report with counts and validation errors

## Features

- Multi-format support: `CSV`, `JSON`, `XML`, `SQL`, `XLSX`
- Fluent transformation pipeline with `read()`, `filter()`, `map()`, `validate()`, `write()`
- Structured SQL filters for safer queries
- Execution reporting via `ExecutionResult`
- Spreadsheet export hardening for CSV/XLSX formula injection
- XML parsing hardened with `LIBXML_NONET`
- PHPUnit test suite for pipeline behavior and file integrations
- PSR-4 autoloading and PSR-12-friendly code style

## Installation

```bash
composer require gbelsalvador/data-transformer
```

## Requirements

- PHP `>= 8.0`
- `phpoffice/phpspreadsheet` `^5.4`

## Supported Formats

| Format | Read | Write |
| --- | --- | --- |
| CSV | Yes | Yes |
| JSON | Yes | Yes |
| XML | Yes | Yes |
| SQL | Yes | Yes |
| XLSX | Yes | Yes |

## Quick Start

```php
use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\CsvReader;
use Gbelsalvador\DataTransformer\Writers\JsonWriter;

$transformer = new Transformer();

$result = $transformer->transform(
    new CsvReader('input.csv'),
    new JsonWriter('output.json')
);

echo $result->rowsRead();
echo $result->rowsWritten();
```

## Fluent Pipeline

The fluent API is the recommended approach for non-trivial workflows.

```php
use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\CsvReader;
use Gbelsalvador\DataTransformer\Writers\JsonWriter;

$result = (new Transformer())
    ->read(new CsvReader('users.csv'))
    ->filter(fn (array $row) => ($row['active'] ?? null) === '1')
    ->map([
        'id' => 'user_id',
        'full_name' => fn (array $row) => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
        'email' => 'email',
        'country' => 'country',
    ])
    ->validate([
        'id' => 'required|integer',
        'full_name' => 'required|max:120',
        'email' => 'required|email',
        'country' => 'in:FR,BE,CH,CA',
    ])
    ->write(new JsonWriter('clean-users.json'));

echo $result->rowsRead();
echo $result->rowsWritten();
echo $result->rowsInvalid();
print_r($result->validationErrors());
```

## Core Concepts

### Readers

Readers load data from a source and return a normalized PHP array:

```php
[
    ['column1' => 'value1', 'column2' => 'value2'],
    ['column1' => 'value3', 'column2' => 'value4'],
]
```

Available readers:

- `CsvReader`
- `JsonReader`
- `XmlReader`
- `SqlReader`
- `XlsxReader`

### Writers

Writers receive normalized rows and persist them to a target format.

Available writers:

- `CsvWriter`
- `JsonWriter`
- `XmlWriter`
- `SqlWriter`
- `XlsxWriter`

### ExecutionResult

Every completed transformation returns an `ExecutionResult` object with runtime information:

- `rowsRead()`
- `rowsWritten()`
- `rowsFilteredOut()`
- `rowsInvalid()`
- `errorCount()`
- `duration()`
- `validationErrors()`

## Usage Examples

### CSV to JSON

```php
use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\CsvReader;
use Gbelsalvador\DataTransformer\Writers\JsonWriter;

$result = (new Transformer())->transform(
    new CsvReader('products.csv', delimiter: ';'),
    new JsonWriter('products.json')
);
```

### SQL to XLSX

```php
use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\SqlReader;
use Gbelsalvador\DataTransformer\Writers\XlsxWriter;

$pdo = new PDO('mysql:host=localhost;dbname=my_database', 'user', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$result = (new Transformer())->transform(
    new SqlReader(
        pdo: $pdo,
        tableName: 'users',
        columns: ['id', 'name', 'email', 'created_at'],
        filters: [
            'active' => 1,
            'created_at' => [
                'operator' => '>=',
                'value' => '2026-01-01',
            ],
        ]
    ),
    new XlsxWriter('active-users.xlsx', sheetName: 'Users')
);
```

### JSON to XML

```php
use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\JsonReader;
use Gbelsalvador\DataTransformer\Writers\XmlWriter;

$result = (new Transformer())->transform(
    new JsonReader('catalog.json'),
    new XmlWriter('catalog.xml', rootElement: 'catalog', rowElement: 'item')
);
```

## Validation Rules

The current validation layer supports:

- `required`
- `email`
- `numeric`
- `integer`
- `boolean`
- `date`
- `max:<length>`
- `in:value1,value2,value3`
- `same:other_field`

Example:

```php
$transformer->validate([
    'email' => 'required|email',
    'status' => 'required|in:active,inactive,pending',
    'name' => 'max:120',
]);
```

## SQL Safety

`SqlReader` supports two approaches:

1. `filters`
2. `whereClause` + `whereParams`

The recommended approach is `filters`, because it builds parameterized conditions from a structured array.

```php
new SqlReader(
    pdo: $pdo,
    tableName: 'users',
    filters: [
        'active' => 1,
        'role' => [
            'operator' => 'in',
            'value' => ['admin', 'editor'],
        ],
    ]
);
```

Supported structured operators:

- `=`
- `!=`
- `>`
- `>=`
- `<`
- `<=`
- `like`
- `in`
- `is_null`
- `is_not_null`

## Security Notes

The library includes several basic hardening measures:

- SQL identifiers are validated before query construction
- CSV and XLSX exports neutralize formula-like values
- XML reading uses `LIBXML_NONET` to reduce external entity/network risks

These protections help reduce common issues, but you should still treat file paths, SQL configuration, and user-provided inputs as untrusted data in your application layer.

## API Overview

### Transformer

```php
$transformer = new Transformer();

$transformer->read(ReaderInterface $reader);
$transformer->filter(callable $callback);
$transformer->map(callable|array $mapping);
$transformer->validate(array $rules);
$result = $transformer->write(WriterInterface $writer);
```

### Reader Signatures

```php
new CsvReader(
    string $filePath,
    string $delimiter = ',',
    bool $hasHeader = true,
    string $enclosure = '"',
    string $escape = '\\'
);

new JsonReader(
    string $filePath,
    bool $assoc = true
);

new XmlReader(
    string $filePath,
    string $rootElement = 'root'
);

new SqlReader(
    PDO $pdo,
    string $tableName,
    array $columns = ['*'],
    ?string $whereClause = null,
    array $whereParams = [],
    array $filters = []
);

new XlsxReader(
    string $filePath,
    ?string $sheetName = null,
    bool $hasHeader = true
);
```

### Writer Signatures

```php
new CsvWriter(
    string $filePath,
    string $delimiter = ',',
    string $enclosure = '"',
    bool $includeHeader = true
);

new JsonWriter(
    string $filePath,
    int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
);

new XmlWriter(
    string $filePath,
    string $rootElement = 'root',
    string $rowElement = 'row'
);

new SqlWriter(
    PDO $pdo,
    string $tableName,
    bool $truncateFirst = false
);

new XlsxWriter(
    string $filePath,
    ?string $sheetName = null
);
```

## Testing

Install development dependencies:

```bash
composer install
```

Run the test suite:

```bash
composer test
```

Current coverage includes:

- pipeline behavior
- validation behavior
- CSV/JSON/XML integration flows
- SQL reader/writer tests when `pdo_sqlite` is available

Note: SQL integration tests are skipped automatically if the `pdo_sqlite` driver is not enabled in your PHP environment.

## Project Structure

```text
src/
  Contracts/
  Core/
  Exceptions/
  Readers/
  Writers/

Tests/
  FileIntegrationTest.php
  SqlReadWriteTest.php
  TransformerPipelineTest.php
```

## Roadmap

Planned improvements for upcoming versions:

- streaming for large datasets
- richer schema validation
- more transformation primitives
- append/merge output strategies
- additional input sources such as HTTP or YAML

## Contributing

Contributions, bug reports, and improvement ideas are welcome.

If you want to contribute:

1. Fork the repository
2. Create a feature branch
3. Add or update tests
4. Open a pull request with a clear description

## License

This project is released under the [MIT License](LICENSE).
