<?php

namespace App\Services\EDocument\Standards\Verifactu\Models;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Illuminate\Support\Facades\Log;

class Invoice extends BaseXmlModel
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

    public function __construct()
    {
        // Initialize required properties
        $this->desglose = new Desglose();
        $this->encadenamiento = new Encadenamiento();
        $this->sistemaInformatico = new SistemaInformatico();
        $this->tipoFactura = 'F1'; // Default to normal invoice
    }

    // Getters and setters for all properties
    public function getIdVersion(): string
    {
        return $this->idVersion;
    }

    public function setIdVersion(string $idVersion): self
    {
        $this->idVersion = $idVersion;
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
        if ($destinatarios !== null && count($destinatarios) > 1000) {
            throw new \InvalidArgumentException('Maximum number of recipients (1000) exceeded');
        }
        
        // Ensure all elements are PersonaFisicaJuridica instances
        if ($destinatarios !== null) {
            foreach ($destinatarios as $destinatario) {
                if (!($destinatario instanceof PersonaFisicaJuridica)) {
                    throw new \InvalidArgumentException('All recipients must be instances of PersonaFisicaJuridica');
                }
            }
        }
        
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

    public function setImporteTotal(float $importeTotal): self
    {
        $this->importeTotal = $importeTotal;
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

    protected function signXml(\DOMDocument $doc): void
    {
        if (!$this->privateKeyPath || !file_exists($this->privateKeyPath)) {
            throw new \RuntimeException('Private key not found or not set');
        }

        if (!$this->certificatePath || !file_exists($this->certificatePath)) {
            throw new \RuntimeException('Certificate not found or not set');
        }

        Log::info('Starting XML signing process');
        Log::debug('XML before signing: ' . $doc->saveXML());

        try {
            // Create a new Security object
            Log::debug('Creating XMLSecurityDSig object');
            $objDSig = new XMLSecurityDSig();
            
            // Set canonicalization method
            Log::debug('Setting canonicalization method');
            $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
            
            // Create a new Security key
            Log::debug('Creating XMLSecurityKey object');
            $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
            
            // Load the private key
            Log::debug('Loading private key from: ' . $this->privateKeyPath);
            $objKey->loadKey($this->privateKeyPath, true);
            
            // Add reference
            Log::debug('Adding reference to document');
            $objDSig->addReference(
                $doc,
                XMLSecurityDSig::SHA256,
                ['http://www.w3.org/2000/09/xmldsig#enveloped-signature']
            );
            Log::debug('Added reference to document');
            
            // Add the certificate
            Log::debug('Adding certificate');
            $objDSig->add509Cert(file_get_contents($this->certificatePath));
            
            // Sign the XML document
            Log::debug('Signing document');
            $objDSig->sign($objKey);
            
            // Append the signature to the XML
            Log::debug('Appending signature');
            $objDSig->appendSignature($doc->documentElement);
            
            Log::debug('XML after signing: ' . $doc->saveXML());
        } catch (\Exception $e) {
            Log::error('Error during XML signing: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    public function verifySignature(\DOMDocument $doc): bool
    {
        if (!$this->publicKeyPath || !file_exists($this->publicKeyPath)) {
            throw new \RuntimeException('Public key not found or not set');
        }

        // Get the signature node
        $objXMLSecDSig = new XMLSecurityDSig();
        
        // Locate the signature
        $objDSig = $objXMLSecDSig->locateSignature($doc);
        if (!$objDSig) {
            throw new \RuntimeException('Signature not found in document');
        }
        
        // Canonicalize the signed info
        $objXMLSecDSig->canonicalizeSignedInfo();
        
        // Validate references
        $objXMLSecDSig->validateReference();
        
        // Get the key from the certificate
        $objKey = $objXMLSecDSig->locateKey();
        if (!$objKey) {
            throw new \RuntimeException('Key not found in signature');
        }
        
        // Load the public key
        $objKey->loadKey($this->publicKeyPath, false, true);
        
        // Verify the signature
        return $objXMLSecDSig->verify($objKey) === 1;
    }

    public function toXml(): string
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

        try {
            $doc = new \DOMDocument('1.0', 'UTF-8');
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;

            // Create root element with proper namespaces
            $root = $doc->createElementNS(parent::XML_NAMESPACE, parent::XML_NAMESPACE_PREFIX . ':RegistroAlta');
            $root->setAttribute('xmlns:ds', parent::XML_DS_NAMESPACE);
            $doc->appendChild($root);

            // Add required elements in exact order according to schema
            $root->appendChild($this->createElement($doc, 'IDVersion', $this->idVersion));

            // Create IDFactura structure
            $idFactura = $this->createElement($doc, 'IDFactura');
            $idFactura->appendChild($this->createElement($doc, 'IDEmisorFactura', $this->tercero?->getNif() ?? 'B12345678'));
            $idFactura->appendChild($this->createElement($doc, 'NumSerieFactura', $this->idFactura));
            $idFactura->appendChild($this->createElement($doc, 'FechaExpedicionFactura', date('d-m-Y')));
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

            // Add TipoRectificativa and related elements for rectification invoices
            if ($this->tipoFactura === 'R1' && $this->facturaRectificativa !== null) {
                $root->appendChild($this->createElement($doc, 'TipoRectificativa', $this->facturaRectificativa->getTipoRectificativa()));
                
                // Add FacturasRectificadas
                $facturasRectificadas = $this->createElement($doc, 'FacturasRectificadas');
                $facturasRectificadas->appendChild($this->facturaRectificativa->toXml($doc));
                $root->appendChild($facturasRectificadas);

                // Add ImporteRectificacion
                $importeRectificacion = $this->createElement($doc, 'ImporteRectificacion');
                $importeRectificacion->appendChild($this->createElement($doc, 'BaseRectificada', 
                    number_format($this->facturaRectificativa->getBaseRectificada(), 2, '.', '')));
                $importeRectificacion->appendChild($this->createElement($doc, 'CuotaRectificada', 
                    number_format($this->facturaRectificativa->getCuotaRectificada(), 2, '.', '')));
                
                if ($this->facturaRectificativa->getCuotaRecargoRectificado() !== null) {
                    $importeRectificacion->appendChild($this->createElement($doc, 'CuotaRecargoRectificado', 
                        number_format($this->facturaRectificativa->getCuotaRecargoRectificado(), 2, '.', '')));
                }
                
                $root->appendChild($importeRectificacion);
            }

            $root->appendChild($this->createElement($doc, 'DescripcionOperacion', $this->descripcionOperacion));

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

            // Add tercero if present
            if ($this->tercero !== null) {
                $terceroElement = $this->createElement($doc, 'Tercero');
                $terceroElement->appendChild($this->createElement($doc, 'NombreRazon', $this->tercero->getRazonSocial()));
                $terceroElement->appendChild($this->createElement($doc, 'NIF', $this->tercero->getNif()));
                $root->appendChild($terceroElement);
            }

            // Add destinatarios if present
            if ($this->destinatarios !== null && count($this->destinatarios) > 0) {
                $destinatariosElement = $this->createElement($doc, 'Destinatarios');
                foreach ($this->destinatarios as $destinatario) {
                    $idDestinatarioElement = $this->createElement($doc, 'IDDestinatario');
                    $idDestinatarioElement->appendChild($this->createElement($doc, 'NombreRazon', $destinatario->getNombreRazon() ?? $destinatario->getRazonSocial()));
                    
                    // Handle either NIF or IDOtro
                    if ($destinatario->getNif() !== null) {
                        $idDestinatarioElement->appendChild($this->createElement($doc, 'NIF', $destinatario->getNif()));
                    } else {
                        $idOtroElement = $this->createElement($doc, 'IDOtro');
                        if ($destinatario->getPais() !== null) {
                            $idOtroElement->appendChild($this->createElement($doc, 'CodigoPais', $destinatario->getPais()));
                        }
                        $idOtroElement->appendChild($this->createElement($doc, 'IDType', $destinatario->getTipoIdentificacion()));
                        $idOtroElement->appendChild($this->createElement($doc, 'ID', $destinatario->getIdOtro()));
                        $idDestinatarioElement->appendChild($idOtroElement);
                    }
                    
                    $destinatariosElement->appendChild($idDestinatarioElement);
                }
                $root->appendChild($destinatariosElement);
            }

            // Add desglose
            try {
                $desgloseXml = $this->desglose->toXml();
                $desgloseDoc = new \DOMDocument();
                if (!$desgloseDoc->loadXML($desgloseXml)) {
                    error_log("Failed to load desglose XML");
                    throw new \DOMException('Failed to load desglose XML');
                }
                $desgloseNode = $doc->importNode($desgloseDoc->documentElement, true);
                // Remove any existing namespace declarations
                foreach (['xmlns:sf', 'xmlns:ds'] as $attr) {
                    if ($desgloseNode->hasAttribute($attr)) {
                        $desgloseNode->removeAttribute($attr);
                    }
                }
                $root->appendChild($desgloseNode);
            } catch (\Exception $e) {
                error_log("Error in desglose: " . $e->getMessage());
                throw $e;
            }

            // Add CuotaTotal and ImporteTotal
            $root->appendChild($this->createElement($doc, 'CuotaTotal', number_format($this->cuotaTotal, 2, '.', '')));
            $root->appendChild($this->createElement($doc, 'ImporteTotal', number_format($this->importeTotal, 2, '.', '')));

            // Add encadenamiento
            try {
                $encadenamientoXml = $this->encadenamiento->toXml();
                $encadenamientoDoc = new \DOMDocument();
                if (!$encadenamientoDoc->loadXML($encadenamientoXml)) {
                    error_log("Failed to load encadenamiento XML");
                    throw new \DOMException('Failed to load encadenamiento XML');
                }
                $encadenamientoNode = $doc->importNode($encadenamientoDoc->documentElement, true);
                $root->appendChild($encadenamientoNode);
            } catch (\Exception $e) {
                error_log("Error in encadenamiento: " . $e->getMessage());
                throw $e;
            }

            // Add sistema informatico
            $sistemaElement = $this->createElement($doc, 'SistemaInformatico');
            $sistemaElement->appendChild($this->createElement($doc, 'NombreRazon', $this->sistemaInformatico->getNombreRazon()));
            $sistemaElement->appendChild($this->createElement($doc, 'NIF', $this->sistemaInformatico->getNif()));
            $sistemaElement->appendChild($this->createElement($doc, 'NombreSistemaInformatico', $this->sistemaInformatico->getNombreSistemaInformatico()));
            $sistemaElement->appendChild($this->createElement($doc, 'IdSistemaInformatico', $this->sistemaInformatico->getIdSistemaInformatico()));
            $sistemaElement->appendChild($this->createElement($doc, 'Version', $this->sistemaInformatico->getVersion()));
            $sistemaElement->appendChild($this->createElement($doc, 'NumeroInstalacion', $this->sistemaInformatico->getNumeroInstalacion()));
            $sistemaElement->appendChild($this->createElement($doc, 'TipoUsoPosibleSoloVerifactu', $this->sistemaInformatico->getTipoUsoPosibleSoloVerifactu()));
            $sistemaElement->appendChild($this->createElement($doc, 'TipoUsoPosibleMultiOT', $this->sistemaInformatico->getTipoUsoPosibleMultiOT()));
            $sistemaElement->appendChild($this->createElement($doc, 'IndicadorMultiplesOT', $this->sistemaInformatico->getIndicadorMultiplesOT()));
            $root->appendChild($sistemaElement);

            // Add remaining required fields
            $root->appendChild($this->createElement($doc, 'FechaHoraHusoGenRegistro', $this->fechaHoraHusoGenRegistro));
            
            if ($this->numRegistroAcuerdoFacturacion !== null) {
                $root->appendChild($this->createElement($doc, 'NumRegistroAcuerdoFacturacion', $this->numRegistroAcuerdoFacturacion));
            }

            if ($this->idAcuerdoSistemaInformatico !== null) {
                $root->appendChild($this->createElement($doc, 'IDAcuerdoSistemaInformatico', $this->idAcuerdoSistemaInformatico));
            }

            $root->appendChild($this->createElement($doc, 'TipoHuella', $this->tipoHuella));
            $root->appendChild($this->createElement($doc, 'Huella', $this->huella));

            // Add signature if present
            if ($this->signature !== null) {
                $signatureElement = $this->createDsElement($doc, 'Signature', $this->signature);
                $root->appendChild($signatureElement);
            }

            // Sign the document if private key is set
            if ($this->privateKeyPath !== null) {
                try {
                    $this->signXml($doc);
                } catch (\Exception $e) {
                    throw new \RuntimeException('Failed to sign XML: ' . $e->getMessage());
                }
            }

            return $doc->saveXML();
        } catch (\Exception $e) {
            error_log("Error in toXml: " . $e->getMessage());
            throw new \RuntimeException("Error in toXml: " . $e->getMessage(), 0, $e);
        }
    }

    public static function fromXml($xml): self
    {
        if ($xml instanceof \DOMElement) {
            return static::fromDOMElement($xml);
        }
        
        if (!is_string($xml)) {
            throw new \InvalidArgumentException('Input must be either a string or DOMElement');
        }
        
        // Enable user error handling for XML parsing
        $previousErrorSetting = libxml_use_internal_errors(true);
        
        try {
            $doc = new \DOMDocument();
            if (!$doc->loadXML($xml)) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
                throw new \DOMException('Failed to load XML: ' . ($errors ? $errors[0]->message : 'Invalid XML format'));
            }
            return static::fromDOMElement($doc->documentElement);
        } finally {
            // Restore previous error handling setting
            libxml_use_internal_errors($previousErrorSetting);
        }
    }

    public static function fromDOMElement(\DOMElement $element): self
    {
        $invoice = new self();

        // Parse IDVersion
        $idVersionElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'IDVersion')->item(0);
        if ($idVersionElement) {
            $invoice->setIDVersion($idVersionElement->nodeValue);
        }

        // Parse IDFactura
        $idFacturaElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'IDFactura')->item(0);
        if ($idFacturaElement) {
            $numSerieFacturaElement = $idFacturaElement->getElementsByTagNameNS(self::XML_NAMESPACE, 'NumSerieFactura')->item(0);
            if ($numSerieFacturaElement) {
                $invoice->setIdFactura($numSerieFacturaElement->nodeValue);
            }
        }

        // Parse RefExterna
        $refExternaElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'RefExterna')->item(0);
        if ($refExternaElement) {
            $invoice->setRefExterna($refExternaElement->nodeValue);
        }

        // Parse NombreRazonEmisor
        $nombreRazonEmisorElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'NombreRazonEmisor')->item(0);
        if ($nombreRazonEmisorElement) {
            $invoice->setNombreRazonEmisor($nombreRazonEmisorElement->nodeValue);
        }

        // Parse EmitidaPorTerceroODestinatario
        $emitidaPorTerceroElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'EmitidaPorTerceroODestinatario')->item(0);
        if ($emitidaPorTerceroElement) {
            $invoice->setEmitidaPorTerceroODestinatario($emitidaPorTerceroElement->nodeValue);
        }

        // Parse TipoFactura
        $tipoFacturaElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'TipoFactura')->item(0);
        if ($tipoFacturaElement) {
            $invoice->setTipoFactura($tipoFacturaElement->nodeValue);
        }

        // Parse TipoRectificativa
        $tipoRectificativaElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'TipoRectificativa')->item(0);
        if ($tipoRectificativaElement) {
            $invoice->setTipoRectificativa($tipoRectificativaElement->nodeValue);
        }

        // Parse DescripcionOperacion
        $descripcionOperacionElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'DescripcionOperacion')->item(0);
        if ($descripcionOperacionElement) {
            $invoice->setDescripcionOperacion($descripcionOperacionElement->nodeValue);
        }

        // Parse FacturaSimplificadaArt7273
        $facturaSimplificadaElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'FacturaSimplificadaArt7273')->item(0);
        if ($facturaSimplificadaElement) {
            $invoice->setFacturaSimplificadaArt7273($facturaSimplificadaElement->nodeValue);
        }

        // Parse FacturaSinIdentifDestinatarioArt61d
        $facturaSinIdentifElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'FacturaSinIdentifDestinatarioArt61d')->item(0);
        if ($facturaSinIdentifElement) {
            $invoice->setFacturaSinIdentifDestinatarioArt61d($facturaSinIdentifElement->nodeValue);
        }

        // Parse Macrodato
        $macrodatoElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'Macrodato')->item(0);
        if ($macrodatoElement) {
            $invoice->setMacrodato($macrodatoElement->nodeValue);
        }

        // Parse Tercero
        $terceroElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'Tercero')->item(0);
        if ($terceroElement) {
            $tercero = new PersonaFisicaJuridica();
            
            // Get NombreRazon
            $nombreRazonElement = $terceroElement->getElementsByTagNameNS(self::XML_NAMESPACE, 'NombreRazon')->item(0);
            if ($nombreRazonElement) {
                $tercero->setRazonSocial($nombreRazonElement->nodeValue);
            }
            
            // Get NIF
            $nifElement = $terceroElement->getElementsByTagNameNS(self::XML_NAMESPACE, 'NIF')->item(0);
            if ($nifElement) {
                $tercero->setNif($nifElement->nodeValue);
            }
            
            $invoice->setTercero($tercero);
        }

        // Parse Desglose
        $desgloseElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'Desglose')->item(0);
        if ($desgloseElement) {
            $invoice->setDesglose(Desglose::fromDOMElement($desgloseElement));
        }

        // Parse CuotaTotal
        $cuotaTotalElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'CuotaTotal')->item(0);
        if ($cuotaTotalElement) {
            $invoice->setCuotaTotal((float)$cuotaTotalElement->nodeValue);
        }

        // Parse ImporteTotal
        $importeTotalElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'ImporteTotal')->item(0);
        if ($importeTotalElement) {
            $invoice->setImporteTotal((float)$importeTotalElement->nodeValue);
        }

        // Parse Encadenamiento
        $encadenamientoElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'Encadenamiento')->item(0);
        if ($encadenamientoElement) {
            $invoice->setEncadenamiento(Encadenamiento::fromDOMElement($encadenamientoElement));
        }

        // Parse SistemaInformatico
        $sistemaInformaticoElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'SistemaInformatico')->item(0);
        if ($sistemaInformaticoElement) {
            $invoice->setSistemaInformatico(SistemaInformatico::fromDOMElement($sistemaInformaticoElement));
        }

        // Parse FechaHoraHusoGenRegistro
        $fechaHoraElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'FechaHoraHusoGenRegistro')->item(0);
        if ($fechaHoraElement) {
            $invoice->setFechaHoraHusoGenRegistro($fechaHoraElement->nodeValue);
        }

        // Parse TipoHuella
        $tipoHuellaElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'TipoHuella')->item(0);
        if ($tipoHuellaElement) {
            $invoice->setTipoHuella($tipoHuellaElement->nodeValue);
        }

        // Parse Huella
        $huellaElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'Huella')->item(0);
        if ($huellaElement) {
            $invoice->setHuella($huellaElement->nodeValue);
        }

        // Parse Destinatarios
        $destinatariosElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'Destinatarios')->item(0);
        if ($destinatariosElement) {
            $destinatarios = [];
            $idDestinatarioElements = $destinatariosElement->getElementsByTagNameNS(self::XML_NAMESPACE, 'IDDestinatario');
            foreach ($idDestinatarioElements as $idDestinatarioElement) {
                $destinatario = new PersonaFisicaJuridica();
                
                // Get NombreRazon
                $nombreRazonElement = $idDestinatarioElement->getElementsByTagNameNS(self::XML_NAMESPACE, 'NombreRazon')->item(0);
                if ($nombreRazonElement) {
                    $destinatario->setNombreRazon($nombreRazonElement->nodeValue);
                }
                
                // Get either NIF or IDOtro
                $nifElement = $idDestinatarioElement->getElementsByTagNameNS(self::XML_NAMESPACE, 'NIF')->item(0);
                if ($nifElement) {
                    $destinatario->setNif($nifElement->nodeValue);
                } else {
                    $idOtroElement = $idDestinatarioElement->getElementsByTagNameNS(self::XML_NAMESPACE, 'IDOtro')->item(0);
                    if ($idOtroElement) {
                        $codigoPaisElement = $idOtroElement->getElementsByTagNameNS(self::XML_NAMESPACE, 'CodigoPais')->item(0);
                        $idTypeElement = $idOtroElement->getElementsByTagNameNS(self::XML_NAMESPACE, 'IDType')->item(0);
                        $idElement = $idOtroElement->getElementsByTagNameNS(self::XML_NAMESPACE, 'ID')->item(0);
                        
                        if ($codigoPaisElement) {
                            $destinatario->setPais($codigoPaisElement->nodeValue);
                        }
                        if ($idTypeElement) {
                            $destinatario->setTipoIdentificacion($idTypeElement->nodeValue);
                        }
                        if ($idElement) {
                            $destinatario->setIdOtro($idElement->nodeValue);
                        }
                    }
                }
                
                $destinatarios[] = $destinatario;
            }
            $invoice->setDestinatarios($destinatarios);
        }

        return $invoice;
    }
} 