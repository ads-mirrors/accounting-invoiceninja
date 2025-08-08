<?php

namespace App\Services\EDocument\Standards\Verifactu\Models;

/**
 * RegistroModificacion - Invoice Modification Record
 * 
 * This class represents the modification record information required for Verifactu e-invoicing
 * modification operations. It contains the new/modified invoice data.
 */
class RegistroModificacion extends BaseXmlModel
{
    protected string $idVersion;
    protected string $idFactura;
    protected ?string $refExterna = null;
    protected string $nombreRazonEmisor;
    protected ?string $subsanacion = null;
    protected ?string $rechazoPrevio = null;
    protected string $tipoFactura;
    protected ?string $tipoRectificativa = null;
    protected ?array $facturasRectificadas = null;
    protected ?array $facturasSustituidas = null;
    protected ?float $importeRectificacion = null;
    protected ?string $fechaOperacion = null;
    protected string $descripcionOperacion;
    protected ?string $facturaSimplificadaArt7273 = null;
    protected ?string $facturaSinIdentifDestinatarioArt61d = null;
    protected ?string $macrodato = null;
    protected ?string $emitidaPorTerceroODestinatario = null;
    protected ?PersonaFisicaJuridica $tercero = null;
    protected ?array $destinatarios = null;
    protected ?string $cupon = null;
    protected Desglose $desglose;
    protected float $cuotaTotal;
    protected float $importeTotal;
    protected Encadenamiento $encadenamiento;
    protected SistemaInformatico $sistemaInformatico;
    protected string $fechaHoraHusoGenRegistro;
    protected ?string $numRegistroAcuerdoFacturacion = null;
    protected ?string $idAcuerdoSistemaInformatico = null;
    protected string $tipoHuella;
    protected string $huella;
    protected ?string $signature = null;
    protected ?FacturaRectificativa $facturaRectificativa = null;
    protected ?string $privateKeyPath = null;
    protected ?string $publicKeyPath = null;
    protected ?string $certificatePath = null;
    protected ?string $fechaExpedicionFactura = null;

    public function __construct()
    {
        // Initialize required properties
        $this->desglose = new Desglose();
        $this->encadenamiento = new Encadenamiento();
        $this->sistemaInformatico = new SistemaInformatico();
        $this->tipoFactura = 'F1'; // Default to normal invoice
    }

    // Getters and setters - same as Invoice model
    public function getIdVersion(): string
    {
        return $this->idVersion;
    }

    public function setIdVersion(string $idVersion): self
    {
        $this->idVersion = $idVersion;
        return $this;
    }

    public function getFechaExpedicionFactura(): string
    {
        return $this->fechaExpedicionFactura ?? now()->format('d-m-Y');
    }

    public function setFechaExpedicionFactura(string $fechaExpedicionFactura): self
    {
        $this->fechaExpedicionFactura = $fechaExpedicionFactura;
        return $this;
    }

    public function getIdFactura(): string
    {
        return $this->idFactura;
    }

    public function setIdFactura(string $idFactura): self
    {
        $this->idFactura = $idFactura;
        return $this;
    }

    public function getRefExterna(): ?string
    {
        return $this->refExterna;
    }

    public function setRefExterna(?string $refExterna): self
    {
        $this->refExterna = $refExterna;
        return $this;
    }

    public function getNombreRazonEmisor(): string
    {
        return $this->nombreRazonEmisor;
    }

    public function setNombreRazonEmisor(string $nombreRazonEmisor): self
    {
        $this->nombreRazonEmisor = $nombreRazonEmisor;
        return $this;
    }

    public function getSubsanacion(): ?string
    {
        return $this->subsanacion;
    }

    public function setSubsanacion(?string $subsanacion): self
    {
        $this->subsanacion = $subsanacion;
        return $this;
    }

    public function getRechazoPrevio(): ?string
    {
        return $this->rechazoPrevio;
    }

    public function setRechazoPrevio(?string $rechazoPrevio): self
    {
        $this->rechazoPrevio = $rechazoPrevio;
        return $this;
    }

    public function getTipoFactura(): string
    {
        return $this->tipoFactura;
    }

    public function setTipoFactura(string $tipoFactura): self
    {
        $this->tipoFactura = $tipoFactura;
        return $this;
    }

    public function getTipoRectificativa(): ?string
    {
        return $this->tipoRectificativa;
    }

    public function setTipoRectificativa(?string $tipoRectificativa): self
    {
        $this->tipoRectificativa = $tipoRectificativa;
        return $this;
    }

    public function getFacturasRectificadas(): ?array
    {
        return $this->facturasRectificadas;
    }

    public function setFacturasRectificadas(?array $facturasRectificadas): self
    {
        $this->facturasRectificadas = $facturasRectificadas;
        return $this;
    }

    public function getFacturasSustituidas(): ?array
    {
        return $this->facturasSustituidas;
    }

    public function setFacturasSustituidas(?array $facturasSustituidas): self
    {
        $this->facturasSustituidas = $facturasSustituidas;
        return $this;
    }

    public function getImporteRectificacion(): ?float
    {
        return $this->importeRectificacion;
    }

    public function setImporteRectificacion(?float $importeRectificacion): self
    {
        $this->importeRectificacion = $importeRectificacion;
        return $this;
    }

    public function getFechaOperacion(): ?string
    {
        return $this->fechaOperacion;
    }

    public function setFechaOperacion(?string $fechaOperacion): self
    {
        $this->fechaOperacion = $fechaOperacion;
        return $this;
    }

    public function getDescripcionOperacion(): string
    {
        return $this->descripcionOperacion;
    }

    public function setDescripcionOperacion(string $descripcionOperacion): self
    {
        $this->descripcionOperacion = $descripcionOperacion;
        return $this;
    }

    public function getFacturaSimplificadaArt7273(): ?string
    {
        return $this->facturaSimplificadaArt7273;
    }

    public function setFacturaSimplificadaArt7273(?string $facturaSimplificadaArt7273): self
    {
        $this->facturaSimplificadaArt7273 = $facturaSimplificadaArt7273;
        return $this;
    }

    public function getFacturaSinIdentifDestinatarioArt61d(): ?string
    {
        return $this->facturaSinIdentifDestinatarioArt61d;
    }

    public function setFacturaSinIdentifDestinatarioArt61d(?string $facturaSinIdentifDestinatarioArt61d): self
    {
        $this->facturaSinIdentifDestinatarioArt61d = $facturaSinIdentifDestinatarioArt61d;
        return $this;
    }

    public function getMacrodato(): ?string
    {
        return $this->macrodato;
    }

    public function setMacrodato(?string $macrodato): self
    {
        $this->macrodato = $macrodato;
        return $this;
    }

    public function getEmitidaPorTerceroODestinatario(): ?string
    {
        return $this->emitidaPorTerceroODestinatario;
    }

    public function setEmitidaPorTerceroODestinatario(?string $emitidaPorTerceroODestinatario): self
    {
        $this->emitidaPorTerceroODestinatario = $emitidaPorTerceroODestinatario;
        return $this;
    }

    public function getTercero(): ?PersonaFisicaJuridica
    {
        return $this->tercero;
    }

    public function setTercero(?PersonaFisicaJuridica $tercero): self
    {
        $this->tercero = $tercero;
        return $this;
    }

    public function getDestinatarios(): ?array
    {
        return $this->destinatarios;
    }

    public function setDestinatarios(?array $destinatarios): self
    {
        $this->destinatarios = $destinatarios;
        return $this;
    }

    public function getCupon(): ?string
    {
        return $this->cupon;
    }

    public function setCupon(?string $cupon): self
    {
        $this->cupon = $cupon;
        return $this;
    }

    public function getDesglose(): Desglose
    {
        return $this->desglose;
    }

    public function setDesglose(Desglose $desglose): self
    {
        $this->desglose = $desglose;
        return $this;
    }

    public function getCuotaTotal(): float
    {
        return $this->cuotaTotal;
    }

    public function setCuotaTotal(float $cuotaTotal): self
    {
        $this->cuotaTotal = $cuotaTotal;
        return $this;
    }

    public function getImporteTotal(): float
    {
        return $this->importeTotal;
    }

    public function setImporteTotal($importeTotal): self
    {
        if (!is_numeric($importeTotal)) {
            throw new \InvalidArgumentException('ImporteTotal must be a numeric value');
        }

        $formatted = number_format((float)$importeTotal, 2, '.', '');
        if (!preg_match('/^(\+|-)?\d{1,12}(\.\d{0,2})?$/', $formatted)) {
            throw new \InvalidArgumentException('ImporteTotal must be a number with up to 12 digits and 2 decimal places');
        }

        $this->importeTotal = (float)$importeTotal;
        return $this;
    }

    public function getEncadenamiento(): Encadenamiento
    {
        return $this->encadenamiento;
    }

    public function setEncadenamiento(Encadenamiento $encadenamiento): self
    {
        $this->encadenamiento = $encadenamiento;
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

    public function getFechaHoraHusoGenRegistro(): string
    {
        return $this->fechaHoraHusoGenRegistro;
    }

    public function setFechaHoraHusoGenRegistro(string $fechaHoraHusoGenRegistro): self
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $fechaHoraHusoGenRegistro)) {
            throw new \InvalidArgumentException('Invalid date format for FechaHoraHusoGenRegistro. Expected format: YYYY-MM-DDThh:mm:ss');
        }
        $this->fechaHoraHusoGenRegistro = $fechaHoraHusoGenRegistro;
        return $this;
    }

    public function getNumRegistroAcuerdoFacturacion(): ?string
    {
        return $this->numRegistroAcuerdoFacturacion;
    }

    public function setNumRegistroAcuerdoFacturacion(?string $numRegistroAcuerdoFacturacion): self
    {
        $this->numRegistroAcuerdoFacturacion = $numRegistroAcuerdoFacturacion;
        return $this;
    }

    public function getIdAcuerdoSistemaInformatico(): ?string
    {
        return $this->idAcuerdoSistemaInformatico;
    }

    public function setIdAcuerdoSistemaInformatico(?string $idAcuerdoSistemaInformatico): self
    {
        $this->idAcuerdoSistemaInformatico = $idAcuerdoSistemaInformatico;
        return $this;
    }

    public function getTipoHuella(): string
    {
        return $this->tipoHuella;
    }

    public function setTipoHuella(string $tipoHuella): self
    {
        $this->tipoHuella = $tipoHuella;
        return $this;
    }

    public function getHuella(): string
    {
        return $this->huella;
    }

    public function setHuella(string $huella): self
    {
        $this->huella = $huella;
        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): self
    {
        $this->signature = $signature;
        return $this;
    }

    public function getFacturaRectificativa(): ?FacturaRectificativa
    {
        return $this->facturaRectificativa;
    }

    public function setFacturaRectificativa(FacturaRectificativa $facturaRectificativa): void
    {
        $this->facturaRectificativa = $facturaRectificativa;
    }

    public function setPrivateKeyPath(string $path): self
    {
        $this->privateKeyPath = $path;
        return $this;
    }

    public function setPublicKeyPath(string $path): self
    {
        $this->publicKeyPath = $path;
        return $this;
    }

    public function setCertificatePath(string $path): self
    {
        $this->certificatePath = $path;
        return $this;
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        // Create root element with proper namespaces
        $root = $doc->createElementNS(self::XML_NAMESPACE, self::XML_NAMESPACE_PREFIX . ':RegistroModificacion');
        
        // Add namespaces
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:' . self::XML_NAMESPACE_PREFIX, self::XML_NAMESPACE);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:' . self::XML_DS_NAMESPACE_PREFIX, self::XML_DS_NAMESPACE);

        // Add required elements in exact order according to schema
        $root->appendChild($this->createElement($doc, 'IDVersion', $this->idVersion));

        // Create IDFactura structure
        $idFactura = $this->createElement($doc, 'IDFactura');
        $idFactura->appendChild($this->createElement($doc, 'IDEmisorFactura', $this->tercero?->getNif() ?? 'B12345678'));
        $idFactura->appendChild($this->createElement($doc, 'NumSerieFactura', $this->idFactura));
        $idFactura->appendChild($this->createElement($doc, 'FechaExpedicionFactura', $this->getFechaExpedicionFactura()));
        $root->appendChild($idFactura);
        
        if ($this->refExterna !== null) {
            $root->appendChild($this->createElement($doc, 'RefExterna', $this->refExterna));
        }
        
        $root->appendChild($this->createElement($doc, 'NombreRazonEmisor', $this->nombreRazonEmisor));
        
        if ($this->subsanacion !== null) {
            $root->appendChild($this->createElement($doc, 'Subsanacion', $this->subsanacion));
        }
        
        if ($this->rechazoPrevio !== null) {
            $root->appendChild($this->createElement($doc, 'RechazoPrevio', $this->rechazoPrevio));
        }
        
        $root->appendChild($this->createElement($doc, 'TipoFactura', $this->tipoFactura));

        if ($this->tipoFactura === 'R1' && $this->facturaRectificativa !== null) {
            $root->appendChild($this->createElement($doc, 'TipoRectificativa', $this->facturaRectificativa->getTipoRectificativa()));
            $facturasRectificadas = $this->createElement($doc, 'FacturasRectificadas');
            $facturasRectificadas->appendChild($this->facturaRectificativa->toXml($doc));
            $root->appendChild($facturasRectificadas);
            if ($this->importeRectificacion !== null) {
                $root->appendChild($this->createElement($doc, 'ImporteRectificacion', (string)$this->importeRectificacion));
            }
        }

        if ($this->fechaOperacion) {
            $root->appendChild($this->createElement($doc, 'FechaOperacion', date('d-m-Y', strtotime($this->fechaOperacion))));
        }

        $root->appendChild($this->createElement($doc, 'DescripcionOperacion', $this->descripcionOperacion));

        if ($this->cupon !== null) {
            $root->appendChild($this->createElement($doc, 'Cupon', $this->cupon));
        }

        if ($this->facturaSimplificadaArt7273 !== null) {
            $root->appendChild($this->createElement($doc, 'FacturaSimplificadaArt7273', $this->facturaSimplificadaArt7273));
        }

        if ($this->facturaSinIdentifDestinatarioArt61d !== null) {
            $root->appendChild($this->createElement($doc, 'FacturaSinIdentifDestinatarioArt61d', $this->facturaSinIdentifDestinatarioArt61d));
        }

        if ($this->macrodato !== null) {
            $root->appendChild($this->createElement($doc, 'Macrodato', $this->macrodato));
        }

        if ($this->emitidaPorTerceroODestinatario !== null) {
            $root->appendChild($this->createElement($doc, 'EmitidaPorTerceroODestinatario', $this->emitidaPorTerceroODestinatario));
        }

        if ($this->tercero !== null) {
            $root->appendChild($this->tercero->toXml($doc));
        }

        if ($this->destinatarios !== null && count($this->destinatarios) > 0) {
            $destinatariosElement = $this->createElement($doc, 'Destinatarios');
            foreach ($this->destinatarios as $destinatario) {
                $idDestinatarioElement = $this->createElement($doc, 'IDDestinatario');
                
                // Add NombreRazon
                $idDestinatarioElement->appendChild($this->createElement($doc, 'NombreRazon', $destinatario->getNombreRazon()));
                
                // Add either NIF or IDOtro
                if ($destinatario->getNif() !== null) {
                    $idDestinatarioElement->appendChild($this->createElement($doc, 'NIF', $destinatario->getNif()));
                } else {
                    $idOtroElement = $this->createElement($doc, 'IDOtro');
                    $idOtroElement->appendChild($this->createElement($doc, 'CodigoPais', $destinatario->getPais()));
                    $idOtroElement->appendChild($this->createElement($doc, 'IDType', $destinatario->getTipoIdentificacion()));
                    $idOtroElement->appendChild($this->createElement($doc, 'ID', $destinatario->getIdOtro()));
                    $idDestinatarioElement->appendChild($idOtroElement);
                }
                
                $destinatariosElement->appendChild($idDestinatarioElement);
            }
            $root->appendChild($destinatariosElement);
        }

        // Add Desglose
        if ($this->desglose !== null) {
            $root->appendChild($this->desglose->toXml($doc));
        }

        // Add CuotaTotal and ImporteTotal
        $root->appendChild($this->createElement($doc, 'CuotaTotal', (string)$this->cuotaTotal));
        $root->appendChild($this->createElement($doc, 'ImporteTotal', (string)$this->importeTotal));

        // Add Encadenamiento
        if ($this->encadenamiento !== null) {
            $root->appendChild($this->encadenamiento->toXml($doc));
        }

        // Add SistemaInformatico
        if ($this->sistemaInformatico !== null) {
            $root->appendChild($this->sistemaInformatico->toXml($doc));
        }

        // Add FechaHoraHusoGenRegistro
        $root->appendChild($this->createElement($doc, 'FechaHoraHusoGenRegistro', $this->fechaHoraHusoGenRegistro));

        // Add NumRegistroAcuerdoFacturacion
        if ($this->numRegistroAcuerdoFacturacion !== null) {
            $root->appendChild($this->createElement($doc, 'NumRegistroAcuerdoFacturacion', $this->numRegistroAcuerdoFacturacion));
        }

        // Add IdAcuerdoSistemaInformatico
        if ($this->idAcuerdoSistemaInformatico !== null) {
            $root->appendChild($this->createElement($doc, 'IdAcuerdoSistemaInformatico', $this->idAcuerdoSistemaInformatico));
        }

        // Add TipoHuella and Huella
        $root->appendChild($this->createElement($doc, 'TipoHuella', $this->tipoHuella));
        $root->appendChild($this->createElement($doc, 'Huella', $this->huella));

        return $root;
    }

    public function toXmlString(): string
    {
        // Validate required fields first, outside of try-catch
        $requiredFields = [
            'idVersion' => 'IDVersion',
            'idFactura' => 'NumSerieFactura',
            'nombreRazonEmisor' => 'NombreRazonEmisor',
            'tipoFactura' => 'TipoFactura',
            'descripcionOperacion' => 'DescripcionOperacion',
            'cuotaTotal' => 'CuotaTotal',
            'importeTotal' => 'ImporteTotal',
            'fechaHoraHusoGenRegistro' => 'FechaHoraHusoGenRegistro',
            'tipoHuella' => 'TipoHuella',
            'huella' => 'Huella'
        ];

        foreach ($requiredFields as $property => $fieldName) {
            if (!isset($this->$property)) {
                throw new \InvalidArgumentException("Missing required field: $fieldName");
            }
        }

        // Enable user error handling for XML operations
        $previousErrorSetting = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $doc = new \DOMDocument('1.0', 'UTF-8');
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;

            // Create root element using toXml method
            $root = $this->toXml($doc);
            $doc->appendChild($root);

            $xml = $doc->saveXML();
            if ($xml === false) {
                throw new \DOMException('Failed to generate XML');
            }

            return $xml;
        } catch (\ErrorException $e) {
            // Convert any libxml errors to DOMException
            $errors = libxml_get_errors();
            libxml_clear_errors();
            if (!empty($errors)) {
                throw new \DOMException($errors[0]->message);
            }
            throw new \DOMException($e->getMessage());
        } finally {
            // Restore previous error handling setting
            libxml_use_internal_errors($previousErrorSetting);
            libxml_clear_errors();
        }
    }

    public static function fromDOMElement(\DOMElement $element): self
    {
        $registroModificacion = new self();

        // Handle IDVersion
        $idVersion = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'IDVersion')->item(0);
        if ($idVersion) {
            $registroModificacion->setIdVersion($idVersion->nodeValue);
        }

        // Handle IDFactura
        $idFactura = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'IDFactura')->item(0);
        if ($idFactura) {
            $numSerieFactura = $idFactura->getElementsByTagNameNS(self::XML_NAMESPACE, 'NumSerieFactura')->item(0);
            if ($numSerieFactura) {
                $registroModificacion->setIdFactura($numSerieFactura->nodeValue);
            }

            $fechaExpedicionFactura = $idFactura->getElementsByTagNameNS(self::XML_NAMESPACE, 'FechaExpedicionFactura')->item(0);
            if ($fechaExpedicionFactura) {
                $registroModificacion->setFechaExpedicionFactura($fechaExpedicionFactura->nodeValue);
            }
        }

        // Handle other fields similar to Invoice model
        // ... (implement other field parsing as needed)

        return $registroModificacion;
    }
} 