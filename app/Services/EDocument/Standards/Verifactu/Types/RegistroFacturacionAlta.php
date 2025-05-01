<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class RegistroFacturacionAlta
{
    /** @var string */
    #[SerializedName('sum1:IDVersion')]
    protected $IDVersion;

    /** @var IDFacturaExpedida */
    #[SerializedName('sum1:IDFactura')]
    protected $IDFactura;

    /** @var string|null Max length 70 characters */
    #[SerializedName('sum1:RefExterna')]
    protected $RefExterna;

    /** @var string Max length 120 characters */
    #[SerializedName('sum1:NombreRazonEmisor')]
    protected $NombreRazonEmisor;

    /** @var Subsanacion|null */
    #[SerializedName('sum1:Subsanacion')]
    protected $Subsanacion;

    /** @var RechazoPrevio|null */
    #[SerializedName('sum1:RechazoPrevio')]
    protected $RechazoPrevio;

    /** @var string */
    #[SerializedName('sum1:TipoFactura')]
    protected $TipoFactura;

    /** @var string|null */
    #[SerializedName('sum1:TipoRectificativa')]
    protected $TipoRectificativa;

    /** @var IDFacturaAR[]|null */
    #[SerializedName('sum1:FacturasRectificadas')]
    protected $FacturasRectificadas = [];

    /** @var IDFacturaAR[]|null */
    #[SerializedName('sum1:FacturasSustituidas')]
    protected $FacturasSustituidas = [];

    /** @var DesgloseRectificacion|null */
    #[SerializedName('sum1:ImporteRectificacion')]
    protected $ImporteRectificacion;

    /** @var string|null */
    #[SerializedName('sum1:FechaOperacion')]
    protected $FechaOperacion;

    /** @var string Max length 500 characters */
    #[SerializedName('sum1:DescripcionOperacion')]
    protected $DescripcionOperacion;

    /** @var string|null */
    #[SerializedName('sum1:FacturaSimplificadaArt7273')]
    protected $FacturaSimplificadaArt7273;

    /** @var string|null */
    #[SerializedName('sum1:FacturaSinIdentifDestinatarioArt61d')]
    protected $FacturaSinIdentifDestinatarioArt61d;

    /** @var string|null */
    #[SerializedName('sum1:Macrodato')]
    protected $Macrodato;

    /** @var string|null */
    #[SerializedName('sum1:EmitidaPorTerceroODestinatario')]
    protected $EmitidaPorTerceroODestinatario;

    /** @var PersonaFisicaJuridica|null */
    #[SerializedName('sum1:Tercero')]
    protected $Tercero;

    /** @var PersonaFisicaJuridica[]|null */
    #[SerializedName('sum1:Destinatarios')]
    protected $Destinatarios = [];

    /** @var array|null */
    #[SerializedName('sum1:Cupon')]
    protected $Cupon;

    /** @var Desglose */
    #[SerializedName('sum1:Desglose')]
    protected $Desglose;

    /** @var float */
    #[SerializedName('sum1:CuotaTotal')]
    protected $CuotaTotal;

    /** @var float */
    #[SerializedName('sum1:ImporteTotal')]
    protected $ImporteTotal;

    /** @var array */
    #[SerializedName('sum1:Encadenamiento')]
    protected $Encadenamiento;

    /** @var SistemaInformatico */
    #[SerializedName('sum1:SistemaInformatico')]
    protected $SistemaInformatico;

    /** @var \DateTime */
    #[SerializedName('sum1:FechaHoraHusoGenRegistro')]
    protected $FechaHoraHusoGenRegistro;

    /** @var string|null Max length 15 characters */
    #[SerializedName('sum1:NumRegistroAcuerdoFacturacion')]
    protected $NumRegistroAcuerdoFacturacion;

    /** @var string|null Max length 16 characters */
    #[SerializedName('sum1:IDAcuerdoSistemaInformatico')]
    protected $IDAcuerdoSistemaInformatico;

    /** @var string */
    #[SerializedName('sum1:TipoHuella')]
    protected $TipoHuella;

    /** @var string Max length 64 characters */
    #[SerializedName('sum1:Huella')]
    protected $Huella;

    /** @var string|null */
    #[SerializedName('sum1:Signature')]
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