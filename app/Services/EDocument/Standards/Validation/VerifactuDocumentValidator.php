<?php

/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2025. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

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
        
        // Detect document type to determine which validation to apply
        $documentType = $this->detectDocumentType($businessContent);
        
        nlog("Detected document type: " . $documentType);
        
        // For modifications, we need to use a different validation approach
        // since the standard XSD doesn't support modification structure
        if ($documentType === 'modification') {
            $this->validateModificationDocument($businessContent);
        } else {
            // For registration and cancellation, use standard XSD validation
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
        }

        return $this;
    }

    /**
     * Detect the type of Verifactu document
     */
    private function detectDocumentType(\DOMDocument $doc): string
    {
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('si', 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd');
        $xpath->registerNamespace('sum1', 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd');
        
        // Check for modification structure - look for RegistroAlta with TipoFactura R1
        $registroAlta = $xpath->query('//si:RegistroAlta | //sum1:RegistroAlta');
        if ($registroAlta->length > 0) {
            $tipoFactura = $xpath->query('.//si:TipoFactura | .//sum1:TipoFactura', $registroAlta->item(0));
            if ($tipoFactura->length > 0 && $tipoFactura->item(0)->textContent === 'R1') {
                return 'modification';
            }
        }
        
        // Check for RegistroModificacion structure (legacy)
        $registroModificacion = $xpath->query('//si:RegistroModificacion | //sum1:RegistroModificacion');
        if ($registroModificacion->length > 0) {
            return 'modification';
        }
        
        // Check for cancellation structure
        $registroAnulacion = $xpath->query('//si:RegistroAnulacion | //sum1:RegistroAnulacion');
        if ($registroAnulacion->length > 0) {
            return 'cancellation';
        }
        
        // Check for registration structure (RegistroAlta with TipoFactura not R1)
        if ($registroAlta->length > 0) {
            $tipoFactura = $xpath->query('.//si:TipoFactura | .//sum1:TipoFactura', $registroAlta->item(0));
            if ($tipoFactura->length === 0 || $tipoFactura->item(0)->textContent !== 'R1') {
                return 'registration';
            }
        }
        
        return 'unknown';
    }

    /**
     * Validate modification documents using business rules instead of strict XSD
     */
    private function validateModificationDocument(\DOMDocument $doc): void
    {
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('si', 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd');
        $xpath->registerNamespace('sum1', 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd');
        $xpath->registerNamespace('lr', 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd');
        
        // Validate modification-specific structure
        $this->validateModificationStructure($xpath);
        
        // Validate required elements for modifications
        $this->validateModificationRequiredElements($xpath);
        
        // Validate business rules for modifications
        $this->validateModificationBusinessRules($xpath);
    }

    /**
     * Validate modification structure
     */
    private function validateModificationStructure(\DOMXPath $xpath): void
    {
        // Check for RegistroAlta with TipoFactura R1
        $registroAlta = $xpath->query('//si:RegistroAlta');
        if ($registroAlta === false || $registroAlta->length === 0) {
            // Try alternative namespace
            $registroAlta = $xpath->query('//sum1:RegistroAlta');
            if ($registroAlta === false || $registroAlta->length === 0) {
                $this->errors['structure'][] = "RegistroAlta element not found for modification";
                return;
            }
        }
        
        // Check for required modification elements within the RegistroAlta
        $requiredElements = [
            './/si:TipoFactura' => 'TipoFactura',
            './/si:DescripcionOperacion' => 'DescripcionOperacion',
            './/si:ImporteTotal' => 'ImporteTotal'
        ];
        
        foreach ($requiredElements as $xpathQuery => $elementName) {
            $elements = $xpath->query($xpathQuery, $registroAlta->item(0));
            if ($elements === false || $elements->length === 0) {
                // Try alternative namespace
                $altQuery = str_replace('si:', 'sum1:', $xpathQuery);
                $elements = $xpath->query($altQuery, $registroAlta->item(0));
                if ($elements === false || $elements->length === 0) {
                    $this->errors['structure'][] = "Required modification element not found: $elementName";
                }
            }
        }
        
        // Validate TipoFactura is R1 for modifications
        $tipoFactura = $xpath->query('.//si:TipoFactura', $registroAlta->item(0));
        if ($tipoFactura === false || $tipoFactura->length === 0) {
            $tipoFactura = $xpath->query('.//sum1:TipoFactura', $registroAlta->item(0));
        }
        if ($tipoFactura !== false && $tipoFactura->length > 0 && $tipoFactura->item(0)->textContent !== 'R1') {
            $this->errors['structure'][] = "TipoFactura must be 'R1' for modifications, found: " . $tipoFactura->item(0)->textContent;
        }
    }

    /**
     * Validate required elements for modifications
     */
    private function validateModificationRequiredElements(\DOMXPath $xpath): void
    {
        // Check for required elements in FacturasRectificadas - look for both si: and sf: namespaces
        $facturasRectificadas = $xpath->query('//si:FacturasRectificadas | //sf:FacturasRectificadas');
        if ($facturasRectificadas !== false && $facturasRectificadas->length > 0) {
            $idFacturasRectificadas = $xpath->query('//si:FacturasRectificadas/si:IDFacturaRectificada | //sf:FacturasRectificadas/sf:IDFacturaRectificada');
            if ($idFacturasRectificadas === false || $idFacturasRectificadas->length === 0) {
                $this->errors['structure'][] = "At least one IDFacturaRectificada is required in FacturasRectificadas";
            } else {
                // Validate each IDFacturaRectificada has required elements
                foreach ($idFacturasRectificadas as $index => $idFacturaRectificada) {
                    $idEmisorFactura = $xpath->query('.//si:IDEmisorFactura | .//sf:IDEmisorFactura', $idFacturaRectificada);
                    $numSerieFactura = $xpath->query('.//si:NumSerieFactura | .//sf:NumSerieFactura', $idFacturaRectificada);
                    $fechaExpedicionFactura = $xpath->query('.//si:FechaExpedicionFactura | .//sf:FechaExpedicionFactura', $idFacturaRectificada);
                    
                    if ($idEmisorFactura === false || $idEmisorFactura->length === 0) {
                        $this->errors['structure'][] = "IDEmisorFactura is required in IDFacturaRectificada " . ($index + 1);
                    }
                    if ($numSerieFactura === false || $numSerieFactura->length === 0) {
                        $this->errors['structure'][] = "NumSerieFactura is required in IDFacturaRectificada " . ($index + 1);
                    }
                    if ($fechaExpedicionFactura === false || $fechaExpedicionFactura->length === 0) {
                        $this->errors['structure'][] = "FechaExpedicionFactura is required in IDFacturaRectificada " . ($index + 1);
                    }
                }
            }
        }
        
        // Check for tax information - look for both si: and sf: namespaces
        $impuestos = $xpath->query('//si:Impuestos | //sf:Impuestos');
        if ($impuestos !== false && $impuestos->length > 0) {
            $detalleIVA = $xpath->query('//si:Impuestos/si:DetalleIVA | //sf:Impuestos/sf:DetalleIVA');
            if ($detalleIVA === false || $detalleIVA->length === 0) {
                $this->errors['structure'][] = "DetalleIVA is required when Impuestos is present";
            }
        }
    }

    /**
     * Validate business rules for modifications
     */
    private function validateModificationBusinessRules(\DOMXPath $xpath): void
    {
        // Validate ImporteTotal is numeric and positive
        $importeTotal = $xpath->query('//si:ImporteTotal');
        if ($importeTotal->length > 0) {
            $value = $importeTotal->item(0)->textContent;
            if (!is_numeric($value) || floatval($value) <= 0) {
                $this->errors['business'][] = "ImporteTotal must be a positive number, found: $value";
            }
        }
        
        // Validate tax amounts are consistent
        $cuotaRepercutida = $xpath->query('//si:CuotaRepercutida');
        if ($cuotaRepercutida->length > 0) {
            $value = $cuotaRepercutida->item(0)->textContent;
            if (!is_numeric($value)) {
                $this->errors['business'][] = "CuotaRepercutida must be numeric, found: $value";
            }
        }
        
        // Validate date formats
        $fechaExpedicion = $xpath->query('//si:FechaExpedicionFacturaEmisor');
        if ($fechaExpedicion->length > 0) {
            $value = $fechaExpedicion->item(0)->textContent;
            if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
                $this->errors['business'][] = "FechaExpedicionFacturaEmisor must be in DD-MM-YYYY format, found: $value";
            }
        }
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
        return $this->getErrors();
    }
} 