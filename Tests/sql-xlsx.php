<?php
require_once __DIR__ . '/../vendor/autoload.php';
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
    whereParams: [':actif' => 1, ':date' => '2026-01-01']
);

// Écriture vers XLSX
$writer = new XlsxWriter(
    'rapport_utilisateurs.xlsx',
    sheetName: 'Utilisateurs Actifs'
);

$transformer = new Transformer();
$transformer->transform($reader, $writer);