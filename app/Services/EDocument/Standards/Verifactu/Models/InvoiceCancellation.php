<?php

namespace App\Services\EDocument\Standards\Verifactu\Models;

use App\Models\Invoice;

/**
 * InvoiceCancellation - Invoice Cancellation for Verifactu
 * 
 * This class generates the XML structure for cancelling invoices in the Verifactu system.
 * It follows the specific format required by the Spanish Tax Agency (AEAT).
 */
class InvoiceCancellation extends BaseXmlModel implements XmlModelInterface
{
    protected string $idVersion = '1.1';
    protected string $numSerieFacturaEmisor;
    protected string $fechaExpedicionFacturaEmisor;
    protected string $nifEmisor;
    protected string $huellaFactura;
    protected string $estado = '02'; // 02 means 'Invoice cancelled'
    protected string $descripcionEstado = 'Factura anulada por error';

    public function __construct()
    {
        // Default constructor
    }

    /**
     * Create cancellation from an existing invoice
     */
    public static function fromInvoice(Invoice $invoice, string $huella = ''): self
    {
        $cancellation = new self();
        
        $cancellation->setNumSerieFacturaEmisor($invoice->number);
        $cancellation->setFechaExpedicionFacturaEmisor(\Carbon\Carbon::parse($invoice->date)->format('d-m-Y'));
        $cancellation->setNifEmisor($invoice->company->settings->vat_number ?? 'B12345678');
        $cancellation->setHuellaFactura($huella);
        
        return $cancellation;
    }

    // Getters and Setters
    public function getIdVersion(): string
    {
        return $this->idVersion;
    }

    public function setIdVersion(string $idVersion): self
    {
        $this->idVersion = $idVersion;
        return $this;
    }

    public function getNumSerieFacturaEmisor(): string
    {
        return $this->numSerieFacturaEmisor;
    }

    public function setNumSerieFacturaEmisor(string $numSerieFacturaEmisor): self
    {
        $this->numSerieFacturaEmisor = $numSerieFacturaEmisor;
        return $this;
    }

    public function getFechaExpedicionFacturaEmisor(): string
    {
        return $this->fechaExpedicionFacturaEmisor;
    }

    public function setFechaExpedicionFacturaEmisor(string $fechaExpedicionFacturaEmisor): self
    {
        $this->fechaExpedicionFacturaEmisor = $fechaExpedicionFacturaEmisor;
        return $this;
    }

    public function getNifEmisor(): string
    {
        return $this->nifEmisor;
    }

    public function setNifEmisor(string $nifEmisor): self
    {
        $this->nifEmisor = $nifEmisor;
        return $this;
    }

    public function getHuellaFactura(): string
    {
        return $this->huellaFactura;
    }

    public function setHuellaFactura(string $huellaFactura): self
    {
        $this->huellaFactura = $huellaFactura;
        return $this;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): self
    {
        $this->estado = $estado;
        return $this;
    }

    public function getDescripcionEstado(): string
    {
        return $this->descripcionEstado;
    }

    public function setDescripcionEstado(string $descripcionEstado): self
    {
        $this->descripcionEstado = $descripcionEstado;
        return $this;
    }

    /**
     * Generate the XML structure for the cancellation
     */
    public function toXml(\DOMDocument $doc): \DOMElement
    {
        // Create root element with proper namespaces
        $root = $doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', 'SuministroLRFacturas');
        
        // Add namespaces
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
        $root->setAttribute('Version', $this->idVersion);

        // Create LRFacturaEntrada
        $lrFacturaEntrada = $doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', 'LRFacturaEntrada');
        $root->appendChild($lrFacturaEntrada);

        // Create IDFactura
        $idFactura = $doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', 'IDFactura');
        $lrFacturaEntrada->appendChild($idFactura);

        // Create IDEmisorFactura
        $idEmisorFactura = $doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', 'IDEmisorFactura');
        $idFactura->appendChild($idEmisorFactura);

        // Add NumSerieFacturaEmisor
        $idEmisorFactura->appendChild($doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', 'NumSerieFacturaEmisor', $this->numSerieFacturaEmisor));

        // Add FechaExpedicionFacturaEmisor
        $idEmisorFactura->appendChild($doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', 'FechaExpedicionFacturaEmisor', $this->fechaExpedicionFacturaEmisor));

        // Add NIFEmisor
        $idEmisorFactura->appendChild($doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', 'NIFEmisor', $this->nifEmisor));

        // Add HuellaFactura
        $idEmisorFactura->appendChild($doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', 'HuellaFactura', $this->huellaFactura));

        // Create EstadoFactura
        $estadoFactura = $doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', 'EstadoFactura');
        $lrFacturaEntrada->appendChild($estadoFactura);

        // Add Estado
        $estadoFactura->appendChild($doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', 'Estado', $this->estado));

        // Add DescripcionEstado
        $estadoFactura->appendChild($doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', 'DescripcionEstado', $this->descripcionEstado));

        return $root;
    }

    /**
     * Generate XML string
     */
    public function toXmlString(): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        $root = $this->toXml($doc);
        $doc->appendChild($root);

        return $doc->saveXML();
    }

    /**
     * Generate SOAP envelope for web service communication
     */
    public function toSoapEnvelope(): string
    {
        // Create the SOAP document
        $soapDoc = new \DOMDocument('1.0', 'UTF-8');
        $soapDoc->preserveWhiteSpace = false;
        $soapDoc->formatOutput = true;

        // Create SOAP envelope with namespaces
        $envelope = $soapDoc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Envelope');
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sum', 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd');
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sum1', 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd');
        
        $soapDoc->appendChild($envelope);

        // Create Header
        $header = $soapDoc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Header');
        $envelope->appendChild($header);

        // Create Body
        $body = $soapDoc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Body');
        $envelope->appendChild($body);

        // Create RegFactuSistemaFacturacion
        $regFactu = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd', 'sum:RegFactuSistemaFacturacion');
        $body->appendChild($regFactu);

        // Create Cabecera
        $cabecera = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd', 'sum:Cabecera');
        $regFactu->appendChild($cabecera);

        // Create ObligadoEmision
        $obligadoEmision = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:ObligadoEmision');
        $cabecera->appendChild($obligadoEmision);

        // Add ObligadoEmision content (using default values for now)
        $obligadoEmision->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:NombreRazon', 'Test Company'));
        $obligadoEmision->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:NIF', $this->nifEmisor));

        // Create RegistroFactura
        $registroFactura = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd', 'sum:RegistroFactura');
        $regFactu->appendChild($registroFactura);

        // Import your existing XML into the RegistroFactura
        $yourXmlDoc = new \DOMDocument();
        $yourXmlDoc->loadXML($this->toXmlString());
        
        // Import the root element from your XML
        $importedNode = $soapDoc->importNode($yourXmlDoc->documentElement, true);
        $registroFactura->appendChild($importedNode);

        return $soapDoc->saveXML();
    }

    /**
     * Parse from DOM element
     */
    public static function fromDOMElement(\DOMElement $element): self
    {
        $cancellation = new self();

        // Parse IDVersion
        $idVersion = $element->getAttribute('Version');
        if ($idVersion) {
            $cancellation->setIdVersion($idVersion);
        }

        // Parse LRFacturaEntrada
        $lrFacturaEntrada = $element->getElementsByTagNameNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', 'LRFacturaEntrada')->item(0);
        if ($lrFacturaEntrada) {
            // Parse IDFactura
            $idFactura = $lrFacturaEntrada->getElementsByTagNameNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', 'IDFactura')->item(0);
            if ($idFactura) {
                $idEmisorFactura = $idFactura->getElementsByTagNameNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', 'IDEmisorFactura')->item(0);
                if ($idEmisorFactura) {
                    // Parse NumSerieFacturaEmisor
                    $numSerie = $cancellation->getElementValue($idEmisorFactura, 'NumSerieFacturaEmisor', 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd');
                    if ($numSerie) {
                        $cancellation->setNumSerieFacturaEmisor($numSerie);
                    }

                    // Parse FechaExpedicionFacturaEmisor
                    $fecha = $cancellation->getElementValue($idEmisorFactura, 'FechaExpedicionFacturaEmisor', 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd');
                    if ($fecha) {
                        $cancellation->setFechaExpedicionFacturaEmisor($fecha);
                    }

                    // Parse NIFEmisor
                    $nif = $cancellation->getElementValue($idEmisorFactura, 'NIFEmisor', 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd');
                    if ($nif) {
                        $cancellation->setNifEmisor($nif);
                    }

                    // Parse HuellaFactura
                    $huella = $cancellation->getElementValue($idEmisorFactura, 'HuellaFactura', 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd');
                    if ($huella) {
                        $cancellation->setHuellaFactura($huella);
                    }
                }
            }

            // Parse EstadoFactura
            $estadoFactura = $lrFacturaEntrada->getElementsByTagNameNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', 'EstadoFactura')->item(0);
            if ($estadoFactura) {
                // Parse Estado
                $estado = $cancellation->getElementValue($estadoFactura, 'Estado', 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd');
                if ($estado) {
                    $cancellation->setEstado($estado);
                }

                // Parse DescripcionEstado
                $descripcion = $cancellation->getElementValue($estadoFactura, 'DescripcionEstado', 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd');
                if ($descripcion) {
                    $cancellation->setDescripcionEstado($descripcion);
                }
            }
        }

        return $cancellation;
    }



    /**
     * Serialize for storage
     */
    public function serialize(): string
    {
        return serialize($this);
    }

    /**
     * Unserialize from storage
     */
    public static function unserialize(string $data): self
    {
        $object = unserialize($data);
        
        if (!$object instanceof self) {
            throw new \InvalidArgumentException('Invalid serialized data - not an InvoiceCancellation object');
        }
        
        return $object;
    }
} 