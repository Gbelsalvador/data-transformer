# Data Transformer

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.0-777BB4.svg)](https://www.php.net/)
[![Licence](https://img.shields.io/badge/licence-MIT-blue.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-PHPUnit-success.svg)](phpunit.xml.dist)
[![Standards](https://img.shields.io/badge/style-PSR--12-brightgreen.svg)](https://www.php-fig.org/psr/psr-12/)

`gbelsalvador/data-transformer` est une bibliothèque PHP pour lire, transformer, valider et exporter des données structurées entre plusieurs formats.

Elle est pensée pour les workflows de données concrets :

- importer depuis `CSV`, `JSON`, `XML`, `SQL` ou `XLSX`
- normaliser les données dans une structure PHP cohérente
- filtrer, mapper et valider les lignes via un pipeline fluent
- exporter le résultat dans un autre format

## Pourquoi cette bibliothèque

Data Transformer ne se limite pas à convertir un fichier dans un autre format. La bibliothèque ajoute une vraie couche de traitement entre la lecture et l’écriture.

Avec cette approche, vous pouvez :

- convertir des formats
- renommer ou restructurer des colonnes
- filtrer des lignes selon des règles métier
- valider les données avant export
- récupérer un rapport d’exécution exploitable

## Fonctionnalités

- Support multi-format : `CSV`, `JSON`, `XML`, `SQL`, `XLSX`
- Pipeline fluent avec `read()`, `filter()`, `map()`, `validate()`, `write()`
- Filtres SQL structurés pour des requêtes plus sûres
- Rapport d’exécution via `ExecutionResult`
- Protection des exports CSV/XLSX contre les formules malveillantes
- Lecture XML durcie avec `LIBXML_NONET`
- Suite de tests PHPUnit pour le pipeline et les intégrations principales
- Autoload PSR-4 et code compatible PSR-12

## Installation

```bash
composer require gbelsalvador/data-transformer
```

## Prérequis

- PHP `>= 8.0`
- `phpoffice/phpspreadsheet` `^5.4`

## Formats supportés

| Format | Lecture | Écriture |
| --- | --- | --- |
| CSV | Oui | Oui |
| JSON | Oui | Oui |
| XML | Oui | Oui |
| SQL | Oui | Oui |
| XLSX | Oui | Oui |

## Démarrage rapide

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

## Pipeline fluent

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
```

## Règles de validation

Les règles actuellement supportées :

- `required`
- `email`
- `numeric`
- `integer`
- `boolean`
- `date`
- `max:<longueur>`
- `in:valeur1,valeur2,valeur3`
- `same:other_field`

## Sécurité

La bibliothèque inclut plusieurs protections de base :

- validation des identifiants SQL avant construction des requêtes
- neutralisation des valeurs de type formule dans les exports CSV/XLSX
- lecture XML avec `LIBXML_NONET`

## Tests

```bash
composer install
composer test
```

## Structure du projet

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

## Contribution

Les contributions, suggestions et retours sont les bienvenus.

## Licence

Ce projet est distribué sous licence [MIT](LICENSE).
