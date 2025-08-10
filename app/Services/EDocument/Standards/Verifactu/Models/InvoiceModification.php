<?php

namespace App\Services\EDocument\Standards\Verifactu\Models;

/**
 * InvoiceModification - Complete Invoice Modification Container
 *
 * This class represents the complete modification structure required for Verifactu e-invoicing
 * modification operations. It contains both the cancellation record and the modification record.
 */
class InvoiceModification extends BaseXmlModel implements XmlModelInterface
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

    public function setHuella(string $huella): self
    {
        $this->getRegistroModificacion()->setHuella($huella);
        return $this;
    }

    /**
     * Create a modification from an existing invoice
     */
    public static function createFromInvoice(Invoice $originalInvoice, Invoice $modifiedInvoice): self
    {
        $currentTimestamp = now()->format('Y-m-d\TH:i:sP');

        $modification = new self();

        // Set up cancellation record
        $cancellation = new RegistroAnulacion();
        $cancellation
            ->setIdEmisorFactura($originalInvoice->getTercero()?->getNif() ?? 'B12345678')
            ->setNumSerieFactura($originalInvoice->getIdFactura()->getNumSerieFactura())
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
            ->setTipoFactura('R1') // always R1 for rectification
            ->setTipoRectificativa('S') // always S for rectification
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
            ->setFechaHoraHusoGenRegistro($currentTimestamp)
            ->setNumRegistroAcuerdoFacturacion($modifiedInvoice->getNumRegistroAcuerdoFacturacion())
            ->setIdAcuerdoSistemaInformatico($modifiedInvoice->getIdAcuerdoSistemaInformatico())
            ->setTipoHuella($modifiedInvoice->getTipoHuella())
            ->setHuella('PLACEHOLDER_HUELLA');

        $modification->setRegistroModificacion($modificationRecord);

        // Set up sistema informatico for the modification (only if not null)
        if ($modifiedInvoice->getSistemaInformatico()) {
            $modification->setSistemaInformatico($modifiedInvoice->getSistemaInformatico());
        }

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

        // Create RegFactuSistemaFacturacion
        $regFactu = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd', 'sum:RegFactuSistemaFacturacion');
        $body->appendChild($regFactu);

        // Create Cabecera
        $cabecera = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd', 'sum:Cabecera');
        $regFactu->appendChild($cabecera);

        // Create ObligadoEmision
        $obligadoEmision = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:ObligadoEmision');
        $cabecera->appendChild($obligadoEmision);

        // Add ObligadoEmision content
        if ($this->sistemaInformatico) {
            $obligadoEmision->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:NombreRazon', $this->sistemaInformatico->getNombreRazon()));
            $obligadoEmision->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:NIF', $this->sistemaInformatico->getNif()));
        } else {
            // Default values if no sistema informatico is available
            $obligadoEmision->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:NombreRazon', 'CERTIFICADO FISICA PRUEBAS'));
            $obligadoEmision->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:NIF', 'A39200019'));
        }

        // Create RegistroFactura
        $registroFactura = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd', 'sum:RegistroFactura');
        $regFactu->appendChild($registroFactura);

        // Create RegistroAlta
        $registroAlta = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:RegistroAlta');
        $registroFactura->appendChild($registroAlta);

        // Add IDVersion inside RegistroAlta
        $registroAlta->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:IDVersion', '1.0'));

        // Create IDFactura
        $idFactura = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:IDFactura');
        $registroAlta->appendChild($idFactura);

        // Add IDFactura child elements
        $idFactura->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:IDEmisorFactura', $this->registroModificacion->getIdFactura()->getIdEmisorFactura()));
        $idFactura->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:NumSerieFactura', $this->registroModificacion->getIdFactura()->getNumSerieFactura()));
        $idFactura->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:FechaExpedicionFactura', $this->registroModificacion->getIdFactura()->getFechaExpedicionFactura()));

        // Add NombreRazonEmisor
        $registroAlta->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:NombreRazonEmisor', $this->registroModificacion->getNombreRazonEmisor()));

        // Add TipoFactura (R1 for rectificativa)
        $registroAlta->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:TipoFactura', 'R1'));

        // Add TipoRectificativa for R1 invoices (S for sustitutiva)
        if ($this->registroModificacion->getTipoFactura() === 'R1') {
            $registroAlta->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:TipoRectificativa', 'S'));
        }

        // Add DescripcionOperacion
        $registroAlta->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:DescripcionOperacion', $this->registroModificacion->getDescripcionOperacion()));

        // Create ModificacionFactura with correct namespace
        $modificacionFactura = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:ModificacionFactura');
        $registroAlta->appendChild($modificacionFactura);

        // Add TipoRectificativa (S for sustitutiva)
        $modificacionFactura->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:TipoRectificativa', 'S'));

        // Create FacturasRectificadas
        $facturasRectificadas = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:FacturasRectificadas');
        $modificacionFactura->appendChild($facturasRectificadas);

        // Add Factura (the original invoice being rectified)
        $factura = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:Factura');
        $facturasRectificadas->appendChild($factura);

        // Add original invoice details
        $factura->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:NumSerieFacturaEmisor', $this->registroAnulacion->getNumSerieFactura()));
        $factura->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:FechaExpedicionFacturaEmisor', $this->registroAnulacion->getFechaExpedicionFactura()));

        // Create Desglose
        $desglose = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:Desglose');
        $registroAlta->appendChild($desglose);

        // Create DetalleDesglose
        $detalleDesglose = $soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:DetalleDesglose');
        $desglose->appendChild($detalleDesglose);

        // Add DetalleDesglose child elements
        $detalleDesglose->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:ClaveRegimen', '01'));
        $detalleDesglose->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:CalificacionOperacion', 'S1'));
        $detalleDesglose->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:TipoImpositivo', '21'));
        $detalleDesglose->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:BaseImponibleOimporteNoSujeto', '200.00'));
        $detalleDesglose->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:CuotaRepercutida', $this->registroModificacion->getCuotaTotal()));

        // Add ImporteTotal
        $registroAlta->appendChild($soapDoc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:ImporteTotal', $this->registroModificacion->getImporteTotal()));

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

    /**
     * Create a proper RegistroAlta structure from the RegistroModificacion data
     */
    // private function createRegistroAltaFromModificacion(\DOMDocument $doc): \DOMElement
    // {
    //     $registroAlta = $doc->createElementNS(self::XML_NAMESPACE, self::XML_NAMESPACE_PREFIX . ':RegistroAlta');

    //     // Add IDVersion
    //     $registroAlta->appendChild($this->createElement($doc, 'IDVersion', $this->registroModificacion->getIdVersion()));

    //     // Create IDFactura structure
    //     $idFactura = $doc->createElementNS(self::XML_NAMESPACE, self::XML_NAMESPACE_PREFIX . ':IDFactura');
    //     $idFactura->appendChild($this->createElement($doc, 'IDEmisorFactura', $this->registroModificacion->getTercero()?->getNif() ?? 'B12345678'));
    //     $idFactura->appendChild($this->createElement($doc, 'NumSerieFactura', $this->registroModificacion->getIdFactura()));
    //     $idFactura->appendChild($this->createElement($doc, 'FechaExpedicionFactura', '2025-01-01'));
    //     $registroAlta->appendChild($idFactura);

    //     // Add other required elements
    //     if ($this->registroModificacion->getRefExterna()) {
    //         $registroAlta->appendChild($this->createElement($doc, 'RefExterna', $this->registroModificacion->getRefExterna()));
    //     }

    //     $registroAlta->appendChild($this->createElement($doc, 'NombreRazonEmisor', $this->registroModificacion->getNombreRazonEmisor()));
    //     $registroAlta->appendChild($this->createElement($doc, 'TipoFactura', $this->registroModificacion->getTipoFactura()));
    //     $registroAlta->appendChild($this->createElement($doc, 'DescripcionOperacion', $this->registroModificacion->getDescripcionOperacion()));

    //     // Add Desglose
    //     $desglose = $doc->createElementNS(self::XML_NAMESPACE, self::XML_NAMESPACE_PREFIX . ':Desglose');
    //     $desgloseFactura = $doc->createElementNS(self::XML_NAMESPACE, self::XML_NAMESPACE_PREFIX . ':DesgloseFactura');
    //     $desgloseFactura->appendChild($this->createElement($doc, 'Impuesto', '01'));
    //     $desgloseFactura->appendChild($this->createElement($doc, 'ClaveRegimen', '01'));
    //     $desgloseFactura->appendChild($this->createElement($doc, 'CalificacionOperacion', 'S1'));
    //     $desgloseFactura->appendChild($this->createElement($doc, 'TipoImpositivo', '21'));
    //     $desgloseFactura->appendChild($this->createElement($doc, 'BaseImponibleOimporteNoSujeto', '100.00'));
    //     $desgloseFactura->appendChild($this->createElement($doc, 'CuotaRepercutida', '21.00'));
    //     $desglose->appendChild($desgloseFactura);
    //     $registroAlta->appendChild($desglose);

    //     $registroAlta->appendChild($this->createElement($doc, 'CuotaTotal', $this->registroModificacion->getCuotaTotal()));
    //     $registroAlta->appendChild($this->createElement($doc, 'ImporteTotal', $this->registroModificacion->getImporteTotal()));

    //     // Add Encadenamiento
    //     $encadenamiento = $doc->createElementNS(self::XML_NAMESPACE, self::XML_NAMESPACE_PREFIX . ':Encadenamiento');
    //     $encadenamiento->appendChild($this->createElement($doc, 'PrimerRegistro', 'S'));
    //     $registroAlta->appendChild($encadenamiento);

    //     // Add SistemaInformatico
    //     $sistemaInformatico = $doc->createElementNS(self::XML_NAMESPACE, self::XML_NAMESPACE_PREFIX . ':SistemaInformatico');
    //     $sistemaInformatico->appendChild($this->createElement($doc, 'NombreRazon', 'Test System'));
    //     $sistemaInformatico->appendChild($this->createElement($doc, 'NIF', 'B12345678'));
    //     $sistemaInformatico->appendChild($this->createElement($doc, 'NombreSistemaInformatico', 'Test Software'));
    //     $sistemaInformatico->appendChild($this->createElement($doc, 'IdSistemaInformatico', '01'));
    //     $sistemaInformatico->appendChild($this->createElement($doc, 'Version', '1.0'));
    //     $sistemaInformatico->appendChild($this->createElement($doc, 'NumeroInstalacion', '001'));
    //     $sistemaInformatico->appendChild($this->createElement($doc, 'TipoUsoPosibleSoloVerifactu', 'S'));
    //     $sistemaInformatico->appendChild($this->createElement($doc, 'TipoUsoPosibleMultiOT', 'S'));
    //     $sistemaInformatico->appendChild($this->createElement($doc, 'IndicadorMultiplesOT', 'S'));
    //     $registroAlta->appendChild($sistemaInformatico);

    //     $registroAlta->appendChild($this->createElement($doc, 'FechaHoraHusoGenRegistro', $this->registroModificacion->getFechaHoraHusoGenRegistro()));
    //     $registroAlta->appendChild($this->createElement($doc, 'TipoHuella', $this->registroModificacion->getTipoHuella()));
    //     $registroAlta->appendChild($this->createElement($doc, 'Huella', $this->registroModificacion->getHuella()));

    //     return $registroAlta;
    // }
}
