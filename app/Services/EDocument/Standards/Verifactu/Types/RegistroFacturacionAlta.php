<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class RegistroFacturacionAlta
{
    /** @var string */
    protected $idVersion;

    /** @var IDFacturaExpedida */
    protected $idFactura;

    /** @var string|null Max length 70 characters */
    protected $refExterna;

    /** @var string Max length 120 characters */
    protected $nombreRazonEmisor;

    /** @var Subsanacion|null */
    protected $subsanacion;

    /** @var RechazoPrevio|null */
    protected $rechazoPrevio;

    /** @var string */
    protected $tipoFactura;

    /** @var string|null */
    protected $tipoRectificativa;

    /** @var IDFacturaAR[]|null */
    protected $facturasRectificadas = [];

    /** @var IDFacturaAR[]|null */
    protected $facturasSustituidas = [];

    /** @var DesgloseRectificacion|null */
    protected $importeRectificacion;

    /** @var string|null */
    protected $fechaOperacion;

    /** @var string Max length 500 characters */
    protected $descripcionOperacion;

    /** @var string|null */
    protected $facturaSimplificadaArt7273;

    /** @var string|null */
    protected $facturaSinIdentifDestinatarioArt61d;

    /** @var string|null */
    protected $macrodato;

    /** @var string|null */
    protected $emitidaPorTerceroODestinatario;

    /** @var PersonaFisicaJuridica|null */
    protected $tercero;

    /** @var PersonaFisicaJuridica[]|null */
    protected $destinatarios = [];

    /** @var array|null */
    protected $cupon;

    /** @var Desglose */
    protected $desglose;

    /** @var float */
    protected $cuotaTotal;

    /** @var float */
    protected $importeTotal;

    /** @var array */
    protected $encadenamiento;

    /** @var SistemaInformatico */
    protected $sistemaInformatico;

    /** @var \DateTime */
    protected $fechaHoraHusoGenRegistro;

    /** @var string|null Max length 15 characters */
    protected $numRegistroAcuerdoFacturacion;

    /** @var string|null Max length 16 characters */
    protected $idAcuerdoSistemaInformatico;

    /** @var string */
    protected $tipoHuella;

    /** @var string Max length 64 characters */
    protected $huella;

    /** @var string|null */
    protected $signature;

    // Getters and setters with validation

    public function getIdVersion(): string
    {
        return $this->idVersion;
    }

    public function setIdVersion(string $idVersion): self
    {
        $this->idVersion = $idVersion;
        return $this;
    }

    public function getIdFactura(): IDFacturaExpedida
    {
        return $this->idFactura;
    }

    public function setIdFactura(IDFacturaExpedida $idFactura): self
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
        if ($refExterna !== null && strlen($refExterna) > 70) {
            throw new \InvalidArgumentException('RefExterna must not exceed 70 characters');
        }
        $this->refExterna = $refExterna;
        return $this;
    }

    public function getNombreRazonEmisor(): string
    {
        return $this->nombreRazonEmisor;
    }

    public function setNombreRazonEmisor(string $nombreRazonEmisor): self
    {
        if (strlen($nombreRazonEmisor) > 120) {
            throw new \InvalidArgumentException('NombreRazonEmisor must not exceed 120 characters');
        }
        $this->nombreRazonEmisor = $nombreRazonEmisor;
        return $this;
    }

    // Add remaining getters and setters with appropriate validation...

    /**
     * @return PersonaFisicaJuridica[]
     */
    public function getDestinatarios(): array
    {
        return $this->destinatarios;
    }

    public function addDestinatario(PersonaFisicaJuridica $destinatario): self
    {
        if (count($this->destinatarios) >= 1000) {
            throw new \RuntimeException('Maximum number of Destinatarios (1000) exceeded');
        }
        $this->destinatarios[] = $destinatario;
        return $this;
    }

    public function getHuella(): string
    {
        return $this->huella;
    }

    public function setHuella(string $huella): self
    {
        if (strlen($huella) > 64) {
            throw new \InvalidArgumentException('Huella must not exceed 64 characters');
        }
        $this->huella = $huella;
        return $this;
    }
} 