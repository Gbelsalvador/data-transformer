<?php
// src/Readers/XmlReader.php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Readers;

use Gbelsalvador\DataTransformer\Contracts\ReaderInterface;
use Gbelsalvador\DataTransformer\Exceptions\TransformerException;

class XmlReader implements ReaderInterface
{
    private string $filePath;
    private string $rootElement;

    public function __construct(string $filePath, string $rootElement = 'root')
    {
        $this->filePath = $filePath;
        $this->rootElement = $rootElement;
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws TransformerException
     */
    public function read(): array
    {
        if (!file_exists($this->filePath)) {
            throw new TransformerException("XML file not found: {$this->filePath}");
        }

        $xml = simplexml_load_file($this->filePath);
        if ($xml === false) {
            throw new TransformerException("Invalid XML file: {$this->filePath}");
        }

        $data = [];
        foreach ($xml->children() as $child) {
            $data[] = $this->convertXmlToArray($child);
        }

        return $data;
    }

    private function convertXmlToArray(\SimpleXMLElement $element): array
    {
        $array = [];

        foreach ($element->attributes() as $attrName => $attrValue) {
            $array['@' . $attrName] = (string) $attrValue;
        }

        foreach ($element->children() as $child) {
            $childName = $child->getName();
            $childValue = $this->convertXmlToArray($child);

            if (isset($array[$childName])) {
                if (!is_array($array[$childName]) || !isset($array[$childName][0])) {
                    $array[$childName] = [$array[$childName]];
                }
                $array[$childName][] = $childValue;
            } else {
                $array[$childName] = $childValue;
            }
        }

        if (count($array) === 0) {
            return ['_value' => (string) $element];
        }

        return $array;
    }
}