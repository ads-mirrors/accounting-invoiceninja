<?php

namespace App\Services\EDocument\Standards\Validation;

/**
 * VerifactuDocumentValidator - Validates Verifactu XML documents
 * 
 * Extends the base XsltDocumentValidator but is configured specifically for Verifactu
 * validation using the correct XSD schemas and namespaces.
 */
class VerifactuDocumentValidator extends XsltDocumentValidator
{
    private array $verifactu_stylesheets = [
        // Add any Verifactu-specific stylesheets here if needed
        // '/Services/EDocument/Standards/Validation/Verifactu/Stylesheets/verifactu-validation.xslt',
    ];

    private string $verifactu_xsd = 'Services/EDocument/Standards/Verifactu/xsd/SuministroLR.xsd';
    private string $verifactu_informacion_xsd = 'Services/EDocument/Standards/Verifactu/xsd/SuministroInformacion.xsd';

    public function __construct(public string $xml_document)
    {
        parent::__construct($xml_document);
        
        // Override the base configuration for Verifactu
        $this->setXsd($this->verifactu_xsd);
        $this->setStyleSheets($this->verifactu_stylesheets);
    }

    /**
     * Validate Verifactu XML document
     *
     * @return self
     */
    public function validate(): self
    {
        $this->validateVerifactuXsd()
             ->validateVerifactuSchema();

        return $this;
    }

    /**
     * Validate against Verifactu XSD schemas
     */
    private function validateVerifactuXsd(): self
    {
        libxml_use_internal_errors(true);

        $xml = new \DOMDocument();
        $xml->loadXML($this->xml_document);

        // Extract business content from SOAP envelope if needed
        $businessContent = $this->extractBusinessContent($xml);
        
        // Validate against SuministroLR.xsd
        if (!$businessContent->schemaValidate(app_path($this->verifactu_xsd))) {
            $errors = libxml_get_errors();
            libxml_clear_errors();

            foreach ($errors as $error) {
                $this->errors['xsd'][] = sprintf(
                    'Line %d: %s',
                    $error->line,
                    trim($error->message)
                );
            }
        }

        return $this;
    }

    /**
     * Validate against Verifactu-specific schema rules
     */
    private function validateVerifactuSchema(): self
    {
        try {
            // Add any Verifactu-specific validation logic here
            // This could include business rule validation, format checks, etc.
            
            // For now, we'll just do basic structure validation
            $this->validateVerifactuStructure();
            
        } catch (\Throwable $th) {
            $this->errors['general'][] = $th->getMessage();
        }

        return $this;
    }

    /**
     * Extract business content from SOAP envelope
     */
    private function extractBusinessContent(\DOMDocument $doc): \DOMDocument
    {
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('lr', 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd');
        
        $regFactuElements = $xpath->query('//lr:RegFactuSistemaFacturacion');
        
        if ($regFactuElements->length > 0) {
            $businessContent = $regFactuElements->item(0);
            
            $businessDoc = new \DOMDocument();
            $businessDoc->appendChild($businessDoc->importNode($businessContent, true));
            
            return $businessDoc;
        }
        
        // If no business content found, return the original document
        return $doc;
    }

    /**
     * Validate Verifactu-specific structure requirements
     */
    private function validateVerifactuStructure(): void
    {
        $doc = new \DOMDocument();
        $doc->loadXML($this->xml_document);
        
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('si', 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd');
        
        // Check for required elements
        $requiredElements = [
            '//si:TipoFactura',
            '//si:DescripcionOperacion',
            '//si:ImporteTotal'
        ];
        
        foreach ($requiredElements as $element) {
            $nodes = $xpath->query($element);
            if ($nodes->length === 0) {
                $this->errors['structure'][] = "Required element not found: $element";
            }
        }
        
        // Check for modification-specific elements
        $modificationElements = $xpath->query('//si:ModificacionFactura');
        if ($modificationElements->length > 0) {
            // Validate modification structure
            $tipoRectificativa = $xpath->query('//si:TipoRectificativa');
            if ($tipoRectificativa->length === 0) {
                $this->errors['structure'][] = "TipoRectificativa is required for modifications";
            }
            
            $facturasRectificadas = $xpath->query('//si:FacturasRectificadas');
            if ($facturasRectificadas->length === 0) {
                $this->errors['structure'][] = "FacturasRectificadas is required for modifications";
            }
        }
    }

    /**
     * Get Verifactu-specific errors
     */
    public function getVerifactuErrors(): array
    {
        return $this->errors;
    }
} 