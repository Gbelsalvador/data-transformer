<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Gbelsalvador\DataTransformer\Core\Transformer;
use Gbelsalvador\DataTransformer\Readers\JsonReader;
use Gbelsalvador\DataTransformer\Writers\XmlWriter;

$reader = new JsonReader('output.json');

$writer = new XmlWriter(
    'donnees.xml',
    rootElement: 'Name',
    rowElement: 'Sex'
);

$transformer = new Transformer();
$transformer->transform($reader, $writer);