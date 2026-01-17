# Data Transformer

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.0-8892BF.svg)](https://packagist.org/packages/GB/data-transformer)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PSR-12 Compliant](https://img.shields.io/badge/PSR--12-compliant-brightgreen.svg)](https://www.php-fig.org/psr/psr-12/)

Une bibliothèque PHP professionnelle pour transformer des données entre plusieurs formats en utilisant un tableau PHP normalisé comme format intermédiaire.

## 📋 Fonctionnalités

- 🔄 Conversion entre CSV, JSON, XML, SQL et XLSX
- 🏗️ Architecture SOLID et extensible
- 📦 Compatible PSR-4, PSR-1, PSR-12
- 🎯 Typage strict (PHP >= 8.0)
- 🧪 100% testable
- 🚀 Prêt pour Packagist

## 📦 Installation

```bash
composer require Gbelsalvador/data-transformer
```

## 🚀 Utilisation Rapide

### Structure de base

```php
use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\CsvReader;
use Gbelsalvador\DataTransformer\Writers\JsonWriter;

$transformer = new Transformer();
$reader = new CsvReader('input.csv');
$writer = new JsonWriter('output.json');

$transformer->transform($reader, $writer);
```

## 📝 Exemples Complets

### 1. CSV vers JSON

```php
use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\CsvReader;
use Gbelsalvador\DataTransformer\Writers\JsonWriter;

// Configuration avancée du CSV
$reader = new CsvReader(
    'input.csv',
    delimiter: ';',           // Délimiteur personnalisé
    hasHeader: true,          // Détection automatique des en-têtes
    enclosure: '"',           // Caractère d'encadrement
    escape: '\\'              // Caractère d'échappement
);

// Configuration du JSON de sortie
$writer = new JsonWriter(
    'output.json',
    flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
);

$transformer = new Transformer();
$transformer->transform($reader, $writer);
```

### 2. SQL vers XLSX

```php
use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\SqlReader;
use Gbelsalvador\DataTransformer\Writers\XlsxWriter;

// Connexion PDO
$pdo = new PDO('mysql:host=localhost;dbname=ma_base', 'user', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Lecture depuis une table SQL avec conditions
$reader = new SqlReader(
    pdo: $pdo,
    tableName: 'utilisateurs',
    columns: ['id', 'nom', 'email', 'date_inscription'],
    whereClause: 'actif = :actif AND date_inscription > :date',
    whereParams: [':actif' => 1, ':date' => '2024-01-01']
);

// Écriture vers XLSX
$writer = new XlsxWriter(
    'rapport_utilisateurs.xlsx',
    sheetName: 'Utilisateurs Actifs'
);

$transformer = new Transformer();
$transformer->transform($reader, $writer);
```

### 3. XLSX vers CSV avec mapping

```php
use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\XlsxReader;
use Gbelsalvador\DataTransformer\Writers\CsvWriter;

// Lecture d'une feuille spécifique d'un fichier Excel
$reader = new XlsxReader(
    'rapport.xlsx',
    sheetName: 'Feuille1',
    hasHeader: true
);

// CSV avec en-têtes et formatage personnalisé
$writer = new CsvWriter(
    'export.csv',
    delimiter: ',',
    enclosure: '"',
    includeHeader: true
);

$transformer = new Transformer();

// Traitement des données avant écriture (exemple)
$data = $reader->read();

// Transformation/modification des données
$transformedData = array_map(function ($row) {
    // Exemple: formater les dates
    if (isset($row['date'])) {
        $row['date_formatee'] = date('d/m/Y', strtotime($row['date']));
    }
    return $row;
}, $data);

$writer->write($transformedData);
```

### 4. JSON vers XML

```php
use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\JsonReader;
use Gbelsalvador\DataTransformer\Writers\XmlWriter;

$reader = new JsonReader('donnees.json');

$writer = new XmlWriter(
    'donnees.xml',
    rootElement: 'catalogue',
    rowElement: 'produit'
);

$transformer = new Transformer();
$transformer->transform($reader, $writer);
```

### 5. Pipeline de transformation multiple

```php
use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\CsvReader;
use Gbelsalvador\DataTransformer\Writers\JsonWriter;
use Gbelsalvador\DataTransformer\Writers\XmlWriter;

$reader = new CsvReader('input.csv');
$transformer = new Transformer();

// Conversion CSV → JSON
$jsonWriter = new JsonWriter('output.json');
$transformer->transform($reader, $jsonWriter);

// Réinitialisation du reader (ou création d'un nouveau)
$reader2 = new CsvReader('input.csv');
$xmlWriter = new XmlWriter('output.xml');
$transformer->transform($reader2, $xmlWriter);
```

## 🏗️ Architecture

### Structure des dossiers

```
src/
├── Contracts/           # Interfaces
│   ├── ReaderInterface.php
│   └── WriterInterface.php
├── Readers/            # Implémentations des readers
│   ├── CsvReader.php
│   ├── JsonReader.php
│   ├── XmlReader.php
│   ├── SqlReader.php
│   └── XlsxReader.php
├── Writers/            # Implémentations des writers
│   ├── CsvWriter.php
│   ├── JsonWriter.php
│   ├── XmlWriter.php
│   ├── SqlWriter.php
│   └── XlsxWriter.php
├── Core/               # Cœur de l'application
│   └── Transformer.php
└── Exceptions/         # Exceptions personnalisées
    └── TransformerException.php
```

### Format intermédiaire

Tous les readers retournent et tous les writers consomment le même format :

```php
[
    [
        'colonne1' => 'valeur1',
        'colonne2' => 'valeur2',
        // ...
    ],
    [
        'colonne1' => 'valeur3',
        'colonne2' => 'valeur4',
        // ...
    ],
    // ...
]
```

## 🔧 Configuration avancée

### Options CSV

```php
// Reader CSV
new CsvReader(
    string $filePath,
    string $delimiter = ',',
    bool $hasHeader = true,
    string $enclosure = '"',
    string $escape = '\\'
);

// Writer CSV
new CsvWriter(
    string $filePath,
    string $delimiter = ',',
    string $enclosure = '"',
    bool $includeHeader = true
);
```

### Options JSON

```php
// Reader JSON
new JsonReader(
    string $filePath,
    bool $assoc = true
);

// Writer JSON
new JsonWriter(
    string $filePath,
    int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
);
```

### Options XML

```php
// Reader XML
new XmlReader(
    string $filePath,
    string $rootElement = 'root'
);

// Writer XML
new XmlWriter(
    string $filePath,
    string $rootElement = 'root',
    string $rowElement = 'row'
);
```

### Options SQL

```php
// Reader SQL
new SqlReader(
    PDO $pdo,
    string $tableName,
    array $columns = ['*'],
    ?string $whereClause = null,
    array $whereParams = []
);

// Writer SQL
new SqlWriter(
    PDO $pdo,
    string $tableName,
    bool $truncateFirst = false
);
```

### Options XLSX

```php
// Reader XLSX
new XlsxReader(
    string $filePath,
    ?string $sheetName = null,
    bool $hasHeader = true
);

// Writer XLSX
new XlsxWriter(
    string $filePath,
    ?string $sheetName = null
);
```

## 🧪 Tests

```bash
# Installer les dépendances de développement
composer install

# Exécuter les tests
composer test

# Exécuter les tests avec couverture
composer test-coverage
```

## 🚀 Extensibilité

### Créer un nouveau Reader

```php
namespace App\Readers;

use Gbelsalvador\DataTransformer\Contracts\ReaderInterface;

class YamlReader implements ReaderInterface
{
    public function __construct(private string $filePath) {}
    
    public function read(): array
    {
        // Implémentation YAML
        $data = yaml_parse_file($this->filePath);
        return $data ?: [];
    }
}
```

### Créer un nouveau Writer

```php
namespace App\Writers;

use Gbelsalvador\DataTransformer\Contracts\WriterInterface;

class PdfWriter implements WriterInterface
{
    public function __construct(private string $filePath) {}
    
    public function write(array $data): void
    {
        // Implémentation PDF
        $pdf = new FPDF();
        // ... traitement des données
        $pdf->Output($this->filePath, 'F');
    }
}
```

## 📊 Formats supportés

| Format | Lecture | Écriture | Notes |
|--------|---------|----------|-------|
| CSV | ✅ | ✅ | Détection auto des headers, délimiteur configurable |
| JSON | ✅ | ✅ | UTF-8, pretty print, options de flags |
| XML | ✅ | ✅ | Racine configurable, attributs supportés |
| SQL | ✅ | ✅ | PDO uniquement, requêtes dynamiques |
| XLSX | ✅ | ✅ | PhpSpreadsheet, feuilles multiples |

## 🗺️ Roadmap

- [ ] Support YAML
- [ ] Export PDF
- [ ] Validation des données
- [ ] Support streaming (fichiers volumineux)
- [ ] Transformateurs personnalisés (mapping)
- [ ] Outil CLI
- [ ] Plus de drivers de base de données
- [ ] Cache des transformations
- [ ] Support des collections
- [ ] Middleware pipeline

## 🤝 Contribution

Les contributions sont les bienvenues ! Voici comment contribuer :

1. Fork le projet
2. Crée une branche (`git checkout -b feature/amazing-feature`)
3. Commit les changements (`git commit -m 'Add amazing feature'`)
4. Push la branche (`git push origin feature/amazing-feature`)
5. Ouvre une Pull Request

### Standards de code

- Respecter PSR-1, PSR-12
- Utiliser le typage strict
- Ajouter des tests unitaires
- Documenter les nouvelles fonctionnalités

## 📄 Licence

Cette bibliothèque est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 👥 Auteurs

- **GB el salvador** - *Développement initial* - [GitHub](https://github.com/gbelsalvador)

## 🙏 Remerciements

- PHP-FIG pour les standards PSR
- PhpSpreadsheet pour le support XLSX
- Tous les contributeurs

## ⚠️ Dépannage

### Problèmes courants

1. **Fichier non trouvé** : Vérifiez les chemins absolus/relatifs
2. **Permissions** : Assurez-vous d'avoir les droits d'écriture
3. **Encodage** : Utilisez UTF-8 pour tous les fichiers texte
4. **PDO** : Configurez PDO::ATTR_ERRMODE sur ERRMODE_EXCEPTION

### Debug

```php
try {
    $transformer->transform($reader, $writer);
} catch (\Gbelsalvador\DataTransformer\Exceptions\TransformerException $e) {
    echo "Erreur de transformation: " . $e->getMessage();
    // Log ou traitement d'erreur
}
```

## 📞 Support

- [Issues GitHub](https://github.com/GB/data-transformer/issues)
- Documentation : Voir les exemples ci-dessus

---

⭐ Si ce projet vous est utile, n'hésitez pas à lui donner une étoile sur GitHub !
