<?php
// src/Writers/XmlWriter.php
declare(strict_types=1);

namespace Gbelsalvador\DataTransformer\Writers;

use Gbelsalvador\DataTransformer\Contracts\WriterInterface;
use Gbelsalvador\DataTransformer\Exceptions\TransformerException;

class XmlWriter implements WriterInterface
{
    private string $filePath;
    private string $rootElement;
    private string $rowElement;

    public function __construct(string $filePath, string $rootElement = 'root', string $rowElement = 'row')
    {
        $this->filePath = $filePath;
        $this->rootElement = $rootElement;
        $this->rowElement = $rowElement;
    }

    /**
     * @param array<int, array<string, mixed>> $data
     * @throws TransformerException
     */
    public function write(array $data): void
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement($this->rootElement);
        $dom->appendChild($root);

        foreach ($data as $row) {
            $rowElement = $dom->createElement($this->rowElement);
            $this->addArrayToXml($dom, $rowElement, $row);
            $root->appendChild($rowElement);
        }

        $result = $dom->save($this->filePath);
        if ($result === false) {
            throw new TransformerException("Cannot write XML file: {$this->filePath}");
        }
    }

    private function addArrayToXml(\DOMDocument $dom, \DOMElement $element, array $data): void
    {
        foreach ($data as $key => $value) {
            $child = $dom->createElement(is_string($key) ? $key : 'item');
            
            if (is_array($value)) {
                $this->addArrayToXml($dom, $child, $value);
            } else {
                $child->textContent = (string) $value;
            }
            
            $element->appendChild($child);
        }
    }
}