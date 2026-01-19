<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\XlsxReader;
use Gbelsalvador\DataTransformer\Writers\CsvWriter;

// Lecture d'une feuille spécifique d'un fichier Excel
$reader = new XlsxReader(
    'rapport_utilisateurs.xlsx',
    sheetName: 'Utilisateurs Actifs',
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