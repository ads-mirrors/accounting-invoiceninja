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
    protected $Huella = '';

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

    public function getSignature(): ?string
    {
        return $this->Signature;
    }

    public function setSignature(?string $signature): self
    {
        $this->Signature = $signature;
        return $this;
    }

    public function getFechaHoraHusoGenRegistro(): \DateTime
    {
        return $this->FechaHoraHusoGenRegistro;
    }

    public function setFechaHoraHusoGenRegistro(\DateTime $fechaHoraHusoGenRegistro): self
    {
        $this->FechaHoraHusoGenRegistro = $fechaHoraHusoGenRegistro;
        return $this;
    }

    public function getNumRegistroAcuerdoFacturacion(): ?string
    {
        return $this->NumRegistroAcuerdoFacturacion;
    }

    public function setNumRegistroAcuerdoFacturacion(?string $numRegistroAcuerdoFacturacion): self
    {
        if ($numRegistroAcuerdoFacturacion !== null && strlen($numRegistroAcuerdoFacturacion) > 15) {
            throw new \InvalidArgumentException('NumRegistroAcuerdoFacturacion must not exceed 15 characters');
        }
        $this->NumRegistroAcuerdoFacturacion = $numRegistroAcuerdoFacturacion;
        return $this;
    }

    public function getIDAcuerdoSistemaInformatico(): ?string
    {
        return $this->IDAcuerdoSistemaInformatico;
    }

    public function setIDAcuerdoSistemaInformatico(?string $idAcuerdoSistemaInformatico): self
    {
        if ($idAcuerdoSistemaInformatico !== null && strlen($idAcuerdoSistemaInformatico) > 16) {
            throw new \InvalidArgumentException('IDAcuerdoSistemaInformatico must not exceed 16 characters');
        }
        $this->IDAcuerdoSistemaInformatico = $idAcuerdoSistemaInformatico;
        return $this;
    }

    public function getTipoHuella(): string
    {
        return $this->TipoHuella;
    }

    public function setTipoHuella(string $tipoHuella): self
    {
        if ($tipoHuella !== '01') {
            throw new \InvalidArgumentException('TipoHuella must be "01" (SHA-256)');
        }
        $this->TipoHuella = $tipoHuella;
        return $this;
    }

    public function getFacturasRectificadas(): array
    {
        return $this->FacturasRectificadas;
    }

    public function addFacturaRectificada(IDFacturaAR $facturaRectificada): self
    {
        $this->FacturasRectificadas[] = $facturaRectificada;
        return $this;
    }

    public function getFacturasSustituidas(): array
    {
        return $this->FacturasSustituidas;
    }

    public function addFacturaSustituida(IDFacturaAR $facturaSustituida): self
    {
        $this->FacturasSustituidas[] = $facturaSustituida;
        return $this;
    }

    public function getImporteRectificacion(): ?DesgloseRectificacion
    {
        return $this->ImporteRectificacion;
    }

    public function setImporteRectificacion(?DesgloseRectificacion $importeRectificacion): self
    {
        $this->ImporteRectificacion = $importeRectificacion;
        return $this;
    }

    public function getFechaOperacion(): ?string
    {
        return $this->FechaOperacion;
    }

    public function setFechaOperacion(?string $fechaOperacion): self
    {
        if ($fechaOperacion !== null) {
            if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $fechaOperacion)) {
                throw new \InvalidArgumentException('FechaOperacion must be in DD-MM-YYYY format');
            }
            list($day, $month, $year) = explode('-', $fechaOperacion);
            if (!checkdate((int)$month, (int)$day, (int)$year)) {
                throw new \InvalidArgumentException('Invalid date in FechaOperacion');
            }
        }
        $this->FechaOperacion = $fechaOperacion;
        return $this;
    }

    public function getDescripcionOperacion(): string
    {
        return $this->DescripcionOperacion;
    }

    public function setDescripcionOperacion(string $descripcionOperacion): self
    {
        if (strlen($descripcionOperacion) > 500) {
            throw new \InvalidArgumentException('DescripcionOperacion must not exceed 500 characters');
        }
        $this->DescripcionOperacion = $descripcionOperacion;
        return $this;
    }

    public function getFacturaSimplificadaArt7273(): ?string
    {
        return $this->FacturaSimplificadaArt7273;
    }

    public function setFacturaSimplificadaArt7273(?string $facturaSimplificadaArt7273): self
    {
        if ($facturaSimplificadaArt7273 !== null && !in_array($facturaSimplificadaArt7273, ['S', 'N'])) {
            throw new \InvalidArgumentException('FacturaSimplificadaArt7273 must be either "S" or "N"');
        }
        $this->FacturaSimplificadaArt7273 = $facturaSimplificadaArt7273;
        return $this;
    }

    public function getFacturaSinIdentifDestinatarioArt61d(): ?string
    {
        return $this->FacturaSinIdentifDestinatarioArt61d;
    }

    public function setFacturaSinIdentifDestinatarioArt61d(?string $facturaSinIdentifDestinatarioArt61d): self
    {
        if ($facturaSinIdentifDestinatarioArt61d !== null && !in_array($facturaSinIdentifDestinatarioArt61d, ['S', 'N'])) {
            throw new \InvalidArgumentException('FacturaSinIdentifDestinatarioArt61d must be either "S" or "N"');
        }
        $this->FacturaSinIdentifDestinatarioArt61d = $facturaSinIdentifDestinatarioArt61d;
        return $this;
    }

    public function getMacrodato(): ?string
    {
        return $this->Macrodato;
    }

    public function setMacrodato(?string $macrodato): self
    {
        if ($macrodato !== null && !in_array($macrodato, ['S', 'N'])) {
            throw new \InvalidArgumentException('Macrodato must be either "S" or "N"');
        }
        $this->Macrodato = $macrodato;
        return $this;
    }

    public function getEmitidaPorTerceroODestinatario(): ?string
    {
        return $this->EmitidaPorTerceroODestinatario;
    }

    public function setEmitidaPorTerceroODestinatario(?string $emitidaPorTerceroODestinatario): self
    {
        if ($emitidaPorTerceroODestinatario !== null && !in_array($emitidaPorTerceroODestinatario, ['S', 'N'])) {
            throw new \InvalidArgumentException('EmitidaPorTerceroODestinatario must be either "S" or "N"');
        }
        $this->EmitidaPorTerceroODestinatario = $emitidaPorTerceroODestinatario;
        return $this;
    }

    public function getTercero(): ?PersonaFisicaJuridica
    {
        return $this->Tercero;
    }

    public function setTercero(?PersonaFisicaJuridica $tercero): self
    {
        $this->Tercero = $tercero;
        return $this;
    }

    public function getDestinatarios(): array
    {
        return $this->Destinatarios;
    }

    public function addDestinatario(PersonaFisicaJuridica $destinatario): self
    {
        if (count($this->Destinatarios) >= 1000) {
            throw new \InvalidArgumentException('Maximum number of Destinatarios (1000) exceeded');
        }
        $this->Destinatarios[] = $destinatario;
        return $this;
    }

    public function getCupon(): ?array
    {
        return $this->Cupon;
    }

    public function setCupon(?array $cupon): self
    {
        $this->Cupon = $cupon;
        return $this;
    }

    public function getDesglose(): Desglose
    {
        return $this->Desglose;
    }

    public function setDesglose(Desglose $desglose): self
    {
        $this->Desglose = $desglose;
        return $this;
    }

    public function getCuotaTotal(): float
    {
        return $this->CuotaTotal;
    }

    public function setCuotaTotal(float $cuotaTotal): self
    {
        $parts = explode('.', (string)$cuotaTotal);
        $integerPart = $parts[0];
        $decimalPart = $parts[1] ?? '';

        if (strlen($integerPart) > 12) {
            throw new \InvalidArgumentException('CuotaTotal must have at most 12 digits before decimal point');
        }
        if (strlen($decimalPart) > 2) {
            throw new \InvalidArgumentException('CuotaTotal must have at most 2 decimal places');
        }

        $this->CuotaTotal = $cuotaTotal;
        return $this;
    }

    public function getImporteTotal(): float
    {
        return $this->ImporteTotal;
    }

    public function setImporteTotal(float $importeTotal): self
    {
        $parts = explode('.', (string)$importeTotal);
        $integerPart = $parts[0];
        $decimalPart = $parts[1] ?? '';

        if (strlen($integerPart) > 12) {
            throw new \InvalidArgumentException('ImporteTotal must have at most 12 digits before decimal point');
        }
        if (strlen($decimalPart) > 2) {
            throw new \InvalidArgumentException('ImporteTotal must have at most 2 decimal places');
        }

        $this->ImporteTotal = $importeTotal;
        return $this;
    }

    public function getEncadenamiento(): array
    {
        return $this->Encadenamiento;
    }

    public function setEncadenamiento(array $encadenamiento): self
    {
        $this->Encadenamiento = $encadenamiento;
        return $this;
    }

    public function getSistemaInformatico(): SistemaInformatico
    {
        return $this->SistemaInformatico;
    }

    public function setSistemaInformatico(SistemaInformatico $sistemaInformatico): self
    {
        $this->SistemaInformatico = $sistemaInformatico;
        return $this;
    }

    public function toRegistroAlta(): RegistroAlta
    {
        $registroAlta = new RegistroAlta();
        $registroAlta->setIDVersion($this->getIDVersion());
        
        // Convert IDFacturaExpedida to IDFactura
        $idFactura = new IDFactura();
        $idFactura->setIDEmisorFactura($this->getIDFactura()->getIDEmisorFactura());
        $idFactura->setNumSerieFactura($this->getIDFactura()->getNumSerieFactura());
        $idFactura->setFechaExpedicionFactura($this->getIDFactura()->getFechaExpedicionFactura());
        $registroAlta->setIDFactura($idFactura);
        
        $registroAlta->setNombreRazonEmisor($this->getNombreRazonEmisor());
        $registroAlta->setTipoFactura($this->getTipoFactura());
        $registroAlta->setDescripcionOperacion($this->getDescripcionOperacion());
        
        // Convert array of Destinatarios to Destinatarios object
        $destinatarios = new Destinatarios();
        foreach ($this->getDestinatarios() as $destinatario) {
            $destinatarios->addDestinatario($destinatario);
        }
        $registroAlta->setDestinatarios($destinatarios);
        
        $registroAlta->setDesglose($this->getDesglose());
        $registroAlta->setCuotaTotal($this->getCuotaTotal());
        $registroAlta->setImporteTotal($this->getImporteTotal());
        $registroAlta->setSistemaInformatico($this->getSistemaInformatico());
        $registroAlta->setFechaHoraHusoGenRegistro($this->getFechaHoraHusoGenRegistro()->format('Y-m-d\TH:i:sP'));
        $registroAlta->setTipoHuella($this->getTipoHuella());
        $registroAlta->setHuella($this->getHuella());
        
        return $registroAlta;
    }
} 