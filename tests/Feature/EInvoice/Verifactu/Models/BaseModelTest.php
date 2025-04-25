<?php

namespace Tests\Feature\EInvoice\Verifactu\Models;

use PHPUnit\Framework\TestCase;
use App\Services\EDocument\Standards\Verifactu\Models\BaseXmlModel;

abstract class BaseModelTest extends TestCase
{
    protected function assertXmlEquals(string $expectedXml, string $actualXml): void
    {
        $this->assertEquals(
            $this->normalizeXml($expectedXml),
            $this->normalizeXml($actualXml)
        );
    }

    protected function normalizeXml(string $xml): string
    {
        $doc = new \DOMDocument('1.0');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        if (!$doc->loadXML($xml)) {
            throw new \DOMException('Failed to load XML in normalizeXml');
        }
        return $doc->saveXML();
    }

    protected function assertValidatesAgainstXsd(string $xml, string $xsdPath): void
    {
        try {
            $doc = new \DOMDocument();
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;
            if (!$doc->loadXML($xml, LIBXML_NOBLANKS)) {
                throw new \DOMException('Failed to load XML in assertValidatesAgainstXsd');
            }
            
            libxml_use_internal_errors(true);
            $result = $doc->schemaValidate($xsdPath);
            if (!$result) {
                foreach (libxml_get_errors() as $error) {
                }
                libxml_clear_errors();
            }
            
            $this->assertTrue(
                $result,
                'XML does not validate against XSD schema'
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function getTestXsdPath(): string
    {
        return __DIR__ . '/../schema/SuministroInformacion.xsd';
    }
} 