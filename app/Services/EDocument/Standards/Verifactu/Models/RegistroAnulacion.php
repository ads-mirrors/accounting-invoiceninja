<?php

namespace App\Services\EDocument\Standards\Verifactu\Models;

/**
 * RegistroAnulacion - Invoice Cancellation Record
 * 
 * This class represents the cancellation record information required for Verifactu e-invoicing
 * modification operations. It contains the details of the invoice to be cancelled.
 */
class RegistroAnulacion extends BaseXmlModel
{
    protected string $idVersion;
    protected string $idEmisorFactura;
    protected string $numSerieFactura;
    protected string $fechaExpedicionFactura;
    protected string $motivoAnulacion;

    public function __construct()
    {
        $this->idVersion = '1.0';
        $this->motivoAnulacion = '1'; // Default: Sustitución por otra factura
    }

    public function getIdVersion(): string
    {
        return $this->idVersion;
    }

    public function setIdVersion(string $idVersion): self
    {
        $this->idVersion = $idVersion;
        return $this;
    }

    public function getIdEmisorFactura(): string
    {
        return $this->idEmisorFactura;
    }

    public function setIdEmisorFactura(string $idEmisorFactura): self
    {
        $this->idEmisorFactura = $idEmisorFactura;
        return $this;
    }

    public function getNumSerieFactura(): string
    {
        return $this->numSerieFactura;
    }

    public function setNumSerieFactura(string $numSerieFactura): self
    {
        $this->numSerieFactura = $numSerieFactura;
        return $this;
    }

    public function getFechaExpedicionFactura(): string
    {
        return $this->fechaExpedicionFactura;
    }

    public function setFechaExpedicionFactura(string $fechaExpedicionFactura): self
    {
        $this->fechaExpedicionFactura = $fechaExpedicionFactura;
        return $this;
    }

    public function getMotivoAnulacion(): string
    {
        return $this->motivoAnulacion;
    }

    public function setMotivoAnulacion(string $motivoAnulacion): self
    {
        $this->motivoAnulacion = $motivoAnulacion;
        return $this;
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $root = $doc->createElementNS(self::XML_NAMESPACE, self::XML_NAMESPACE_PREFIX . ':RegistroAnulacion');
        
        // Add IDVersion
        $root->appendChild($this->createElement($doc, 'IDVersion', $this->idVersion));
        
        // Create IDFactura structure
        $idFactura = $this->createElement($doc, 'IDFactura');
        $idFactura->appendChild($this->createElement($doc, 'IDEmisorFacturaAnulada', $this->idEmisorFactura));
        $idFactura->appendChild($this->createElement($doc, 'NumSerieFacturaAnulada', $this->numSerieFactura));
        $idFactura->appendChild($this->createElement($doc, 'FechaExpedicionFacturaAnulada', $this->fechaExpedicionFactura));
        $root->appendChild($idFactura);
        
        // Add required elements for RegistroFacturacionAnulacionType with proper values
        $encadenamiento = $doc->createElementNS(self::XML_NAMESPACE, self::XML_NAMESPACE_PREFIX . ':Encadenamiento');
        $encadenamiento->appendChild($this->createElement($doc, 'PrimerRegistro', 'S'));
        $root->appendChild($encadenamiento);
        
        // Add SistemaInformatico with proper structure
        $sistemaInformatico = $doc->createElementNS(self::XML_NAMESPACE, self::XML_NAMESPACE_PREFIX . ':SistemaInformatico');
        $sistemaInformatico->appendChild($this->createElement($doc, 'NombreRazon', 'Test System'));
        $sistemaInformatico->appendChild($this->createElement($doc, 'NIF', 'B12345678'));
        $sistemaInformatico->appendChild($this->createElement($doc, 'NombreSistemaInformatico', 'Test Software'));
        $sistemaInformatico->appendChild($this->createElement($doc, 'IdSistemaInformatico', '01'));
        $sistemaInformatico->appendChild($this->createElement($doc, 'Version', '1.0'));
        $sistemaInformatico->appendChild($this->createElement($doc, 'NumeroInstalacion', '001'));
        $sistemaInformatico->appendChild($this->createElement($doc, 'TipoUsoPosibleSoloVerifactu', 'S'));
        $sistemaInformatico->appendChild($this->createElement($doc, 'TipoUsoPosibleMultiOT', 'S'));
        $sistemaInformatico->appendChild($this->createElement($doc, 'IndicadorMultiplesOT', 'S'));
        $root->appendChild($sistemaInformatico);
        
        $root->appendChild($this->createElement($doc, 'FechaHoraHusoGenRegistro', '2025-01-01T12:00:00'));
        $root->appendChild($this->createElement($doc, 'TipoHuella', '01'));
        $root->appendChild($this->createElement($doc, 'Huella', 'TEST_HASH'));
        
        return $root;
    }

    public static function fromDOMElement(\DOMElement $element): self
    {
        $registroAnulacion = new self();

        // Handle IDVersion
        $idVersion = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'IDVersion')->item(0);
        if ($idVersion) {
            $registroAnulacion->setIdVersion($idVersion->nodeValue);
        }

        // Handle IDFactura
        $idFactura = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'IDFactura')->item(0);
        if ($idFactura) {
            $idEmisorFactura = $idFactura->getElementsByTagNameNS(self::XML_NAMESPACE, 'IDEmisorFactura')->item(0);
            if ($idEmisorFactura) {
                $registroAnulacion->setIdEmisorFactura($idEmisorFactura->nodeValue);
            }

            $numSerieFactura = $idFactura->getElementsByTagNameNS(self::XML_NAMESPACE, 'NumSerieFactura')->item(0);
            if ($numSerieFactura) {
                $registroAnulacion->setNumSerieFactura($numSerieFactura->nodeValue);
            }

            $fechaExpedicionFactura = $idFactura->getElementsByTagNameNS(self::XML_NAMESPACE, 'FechaExpedicionFactura')->item(0);
            if ($fechaExpedicionFactura) {
                $registroAnulacion->setFechaExpedicionFactura($fechaExpedicionFactura->nodeValue);
            }
        }

        // Handle MotivoAnulacion
        $motivoAnulacion = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'MotivoAnulacion')->item(0);
        if ($motivoAnulacion) {
            $registroAnulacion->setMotivoAnulacion($motivoAnulacion->nodeValue);
        }

        return $registroAnulacion;
    }

    public function toXmlString(): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        $root = $this->toXml($doc);
        $doc->appendChild($root);

        return $doc->saveXML();
    }
} 