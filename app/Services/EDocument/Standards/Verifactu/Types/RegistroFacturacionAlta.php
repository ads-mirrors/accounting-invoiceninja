<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class RegistroFacturacionAlta
{
    /** @var string */
    protected $IDVersion;

    /** @var IDFacturaExpedida */
    protected $IDFactura;

    /** @var string|null Max length 70 characters */
    protected $RefExterna;

    /** @var string Max length 120 characters */
    protected $NombreRazonEmisor;

    /** @var Subsanacion|null */
    protected $Subsanacion;

    /** @var RechazoPrevio|null */
    protected $RechazoPrevio;

    /** @var string */
    protected $TipoFactura;

    /** @var string|null */
    protected $TipoRectificativa;

    /** @var IDFacturaAR[]|null */
    protected $FacturasRectificadas = [];

    /** @var IDFacturaAR[]|null */
    protected $FacturasSustituidas = [];

    /** @var DesgloseRectificacion|null */
    protected $ImporteRectificacion;

    /** @var string|null */
    protected $FechaOperacion;

    /** @var string Max length 500 characters */
    protected $DescripcionOperacion;

    /** @var string|null */
    protected $FacturaSimplificadaArt7273;

    /** @var string|null */
    protected $FacturaSinIdentifDestinatarioArt61d;

    /** @var string|null */
    protected $Macrodato;

    /** @var string|null */
    protected $EmitidaPorTerceroODestinatario;

    /** @var PersonaFisicaJuridica|null */
    protected $Tercero;

    /** @var PersonaFisicaJuridica[]|null */
    protected $Destinatarios = [];

    /** @var array|null */
    protected $Cupon;

    /** @var Desglose */
    protected $Desglose;

    /** @var float */
    protected $CuotaTotal;

    /** @var float */
    protected $ImporteTotal;

    /** @var array */
    protected $Encadenamiento;

    /** @var SistemaInformatico */
    protected $SistemaInformatico;

    /** @var \DateTime */
    protected $FechaHoraHusoGenRegistro;

    /** @var string|null Max length 15 characters */
    protected $NumRegistroAcuerdoFacturacion;

    /** @var string|null Max length 16 characters */
    protected $IDAcuerdoSistemaInformatico;

    /** @var string */
    protected $TipoHuella;

    /** @var string Max length 64 characters */
    protected $Huella;

    /** @var string|null */
    protected $Signature;

    // Getters and setters with validation

    public function getIDVersion(): string
    {
        return $this->IDVersion;
    }

    public function setIDVersion(string $idVersion): self
    {
        $this->IDVersion = $idVersion;
        return $this;
    }

    public function getIDFactura(): IDFacturaExpedida
    {
        return $this->IDFactura;
    }

    public function setIDFactura(IDFacturaExpedida $idFactura): self
    {
        $this->IDFactura = $idFactura;
        return $this;
    }

    public function getRefExterna(): ?string
    {
        return $this->RefExterna;
    }

    public function setRefExterna(?string $refExterna): self
    {
        if ($refExterna !== null && strlen($refExterna) > 70) {
            throw new \InvalidArgumentException('RefExterna must not exceed 70 characters');
        }
        $this->RefExterna = $refExterna;
        return $this;
    }

    public function getNombreRazonEmisor(): string
    {
        return $this->NombreRazonEmisor;
    }

    public function setNombreRazonEmisor(string $nombreRazonEmisor): self
    {
        if (strlen($nombreRazonEmisor) > 120) {
            throw new \InvalidArgumentException('NombreRazonEmisor must not exceed 120 characters');
        }
        $this->NombreRazonEmisor = $nombreRazonEmisor;
        return $this;
    }

    // Add remaining getters and setters with appropriate validation...

    /**
     * @return PersonaFisicaJuridica[]
     */
    public function getDestinatarios(): array
    {
        return $this->Destinatarios;
    }

    public function addDestinatario(PersonaFisicaJuridica $destinatario): self
    {
        if (count($this->Destinatarios) >= 1000) {
            throw new \RuntimeException('Maximum number of Destinatarios (1000) exceeded');
        }
        $this->Destinatarios[] = $destinatario;
        return $this;
    }

    public function getHuella(): string
    {
        return $this->Huella;
    }

    public function setHuella(string $huella): self
    {
        if (strlen($huella) > 64) {
            throw new \InvalidArgumentException('Huella must not exceed 64 characters');
        }
        $this->Huella = $huella;
        return $this;
    }
} 