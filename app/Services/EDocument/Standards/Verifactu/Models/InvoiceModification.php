<?php

namespace App\Services\EDocument\Standards\Verifactu\Models;

/**
 * InvoiceModification - Complete Invoice Modification Container
 * 
 * This class represents the complete modification structure required for Verifactu e-invoicing
 * modification operations. It contains both the cancellation record and the modification record.
 */
class InvoiceModification extends BaseXmlModel
{
    protected RegistroAnulacion $registroAnulacion;
    protected RegistroModificacion $registroModificacion;
    protected SistemaInformatico $sistemaInformatico;

    // @todo - in the UI we'll need additional logic to support these codes
    private array $motivo_anulacion_codes = [
        '1' => "Sustitución por otra factura", // Replacement by another invoice
        '2' => "Error en facturación", // Billing error
        '3' => "Anulación por devolución", // Cancellation due to return
        '4' => "Anulación por insolvencia" // Cancellation due to insolvency
    ];

    public function __construct()
    {
        $this->registroAnulacion = new RegistroAnulacion();
        $this->registroModificacion = new RegistroModificacion();
        $this->sistemaInformatico = new SistemaInformatico();
    }

    public function getRegistroAnulacion(): RegistroAnulacion
    {
        return $this->registroAnulacion;
    }

    public function setRegistroAnulacion(RegistroAnulacion $registroAnulacion): self
    {
        $this->registroAnulacion = $registroAnulacion;
        return $this;
    }

    public function getRegistroModificacion(): RegistroModificacion
    {
        return $this->registroModificacion;
    }

    public function setRegistroModificacion(RegistroModificacion $registroModificacion): self
    {
        $this->registroModificacion = $registroModificacion;
        return $this;
    }

    public function getSistemaInformatico(): SistemaInformatico
    {
        return $this->sistemaInformatico;
    }

    public function setSistemaInformatico(SistemaInformatico $sistemaInformatico): self
    {
        $this->sistemaInformatico = $sistemaInformatico;
        return $this;
    }

    /**
     * Create a modification from an existing invoice
     */
    public static function createFromInvoice(Invoice $originalInvoice, Invoice $modifiedInvoice): self
    {
        $modification = new self();

        // Set up cancellation record
        $cancellation = new RegistroAnulacion();
        $cancellation
            ->setIdEmisorFactura($originalInvoice->getTercero()?->getNif() ?? 'B12345678')
            ->setNumSerieFactura($originalInvoice->getIdFactura())
            ->setFechaExpedicionFactura($originalInvoice->getFechaExpedicionFactura())
            ->setMotivoAnulacion('1'); // Sustitución por otra factura

        $modification->setRegistroAnulacion($cancellation);

        // Set up modification record
        $modificationRecord = new RegistroModificacion();
        $modificationRecord
            ->setIdVersion($modifiedInvoice->getIdVersion())
            ->setIdFactura($modifiedInvoice->getIdFactura())
            ->setRefExterna($modifiedInvoice->getRefExterna())
            ->setNombreRazonEmisor($modifiedInvoice->getNombreRazonEmisor())
            ->setSubsanacion($modifiedInvoice->getSubsanacion())
            ->setRechazoPrevio($modifiedInvoice->getRechazoPrevio())
            ->setTipoFactura($modifiedInvoice->getTipoFactura())
            ->setTipoRectificativa($modifiedInvoice->getTipoRectificativa())
            ->setFacturasRectificadas($modifiedInvoice->getFacturasRectificadas())
            ->setFacturasSustituidas($modifiedInvoice->getFacturasSustituidas())
            ->setImporteRectificacion($modifiedInvoice->getImporteRectificacion())
            ->setFechaOperacion($modifiedInvoice->getFechaOperacion())
            ->setDescripcionOperacion($modifiedInvoice->getDescripcionOperacion())
            ->setFacturaSimplificadaArt7273($modifiedInvoice->getFacturaSimplificadaArt7273())
            ->setFacturaSinIdentifDestinatarioArt61d($modifiedInvoice->getFacturaSinIdentifDestinatarioArt61d())
            ->setMacrodato($modifiedInvoice->getMacrodato())
            ->setEmitidaPorTerceroODestinatario($modifiedInvoice->getEmitidaPorTerceroODestinatario())
            ->setTercero($modifiedInvoice->getTercero())
            ->setDestinatarios($modifiedInvoice->getDestinatarios())
            ->setCupon($modifiedInvoice->getCupon())
            ->setDesglose($modifiedInvoice->getDesglose())
            ->setCuotaTotal($modifiedInvoice->getCuotaTotal())
            ->setImporteTotal($modifiedInvoice->getImporteTotal())
            ->setEncadenamiento($modifiedInvoice->getEncadenamiento())
            ->setSistemaInformatico($modifiedInvoice->getSistemaInformatico())
            ->setFechaHoraHusoGenRegistro($modifiedInvoice->getFechaHoraHusoGenRegistro())
            ->setNumRegistroAcuerdoFacturacion($modifiedInvoice->getNumRegistroAcuerdoFacturacion())
            ->setIdAcuerdoSistemaInformatico($modifiedInvoice->getIdAcuerdoSistemaInformatico())
            ->setTipoHuella($modifiedInvoice->getTipoHuella())
            ->setHuella($modifiedInvoice->getHuella());

        $modification->setRegistroModificacion($modificationRecord);

        // Set up sistema informatico for the modification
        $modification->setSistemaInformatico($modifiedInvoice->getSistemaInformatico());

        return $modification;
    }

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

        // Create ModificacionFactura
        $modificacionFactura = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd', 'sum:ModificacionFactura');
        $body->appendChild($modificacionFactura);

        // Add RegistroAnulacion
        $registroAnulacionElement = $this->registroAnulacion->toXml($soapDoc);
        $importedRegistroAnulacion = $soapDoc->importNode($registroAnulacionElement, true);
        $modificacionFactura->appendChild($importedRegistroAnulacion);

        // Add RegistroModificacion
        $registroModificacionElement = $this->registroModificacion->toXml($soapDoc);
        $importedRegistroModificacion = $soapDoc->importNode($registroModificacionElement, true);
        $modificacionFactura->appendChild($importedRegistroModificacion);

        return $soapDoc->saveXML();
    }

    public function toXmlString(): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        // Create ModificacionFactura root
        $root = $doc->createElementNS(self::XML_NAMESPACE, self::XML_NAMESPACE_PREFIX . ':ModificacionFactura');
        $doc->appendChild($root);

        // Add RegistroAnulacion
        $registroAnulacionElement = $this->registroAnulacion->toXml($doc);
        $root->appendChild($registroAnulacionElement);

        // Add RegistroModificacion
        $registroModificacionElement = $this->registroModificacion->toXml($doc);
        $root->appendChild($registroModificacionElement);

        return $doc->saveXML();
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        // Create ModificacionFactura root
        $root = $doc->createElementNS(self::XML_NAMESPACE, self::XML_NAMESPACE_PREFIX . ':ModificacionFactura');

        // Add RegistroAnulacion
        $registroAnulacionElement = $this->registroAnulacion->toXml($doc);
        $root->appendChild($registroAnulacionElement);

        // Add RegistroModificacion
        $registroModificacionElement = $this->registroModificacion->toXml($doc);
        $root->appendChild($registroModificacionElement);

        return $root;
    }

    public static function fromDOMElement(\DOMElement $element): self
    {
        $modification = new self();

        // Handle RegistroAnulacion
        $registroAnulacionElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'RegistroAnulacion')->item(0);
        if ($registroAnulacionElement) {
            $registroAnulacion = RegistroAnulacion::fromDOMElement($registroAnulacionElement);
            $modification->setRegistroAnulacion($registroAnulacion);
        }

        // Handle RegistroModificacion
        $registroModificacionElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'RegistroModificacion')->item(0);
        if ($registroModificacionElement) {
            $registroModificacion = RegistroModificacion::fromDOMElement($registroModificacionElement);
            $modification->setRegistroModificacion($registroModificacion);
        }

        return $modification;
    }
} 