<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class RegistroFactura
{
    /** @var string */
    #[SerializedName('sum1:NumRegistroAcuerdoFacturacion')]
    protected $NumRegistroAcuerdoFacturacion;

    /** @var string */
    #[SerializedName('sum1:FechaHoraHusoGenRegistro')]
    protected $FechaHoraHusoGenRegistro;

    /** @var string */
    #[SerializedName('sum1:Huella')]
    protected $Huella;

    /** @var string|null */
    #[SerializedName('sum1:Signature')]
    protected $Signature;

    /** @var string */
    #[SerializedName('sum1:TipoHuella')]
    protected $TipoHuella;

    /** @var string|null */
    #[SerializedName('sum1:IDAcuerdoSistemaInformatico')]
    protected $IDAcuerdoSistemaInformatico;

    /** @var RegistroAlta */
    #[SerializedName('sum1:RegistroAlta')]
    protected $RegistroAlta;

    /** @var RegistroFacturacionAnulacion|null */
    #[SerializedName('sum1:RegistroAnulacion')]
    protected $RegistroAnulacion;

    public function getNumRegistroAcuerdoFacturacion(): string
    {
        return $this->NumRegistroAcuerdoFacturacion;
    }

    public function setNumRegistroAcuerdoFacturacion(string $numRegistroAcuerdoFacturacion): self
    {
        if (strlen($numRegistroAcuerdoFacturacion) > 15) {
            throw new \InvalidArgumentException('NumRegistroAcuerdoFacturacion must not exceed 15 characters');
        }
        $this->NumRegistroAcuerdoFacturacion = $numRegistroAcuerdoFacturacion;
        return $this;
    }

    public function getFechaHoraHusoGenRegistro(): string
    {
        return $this->FechaHoraHusoGenRegistro;
    }

    public function setFechaHoraHusoGenRegistro(string $fechaHoraHusoGenRegistro): self
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $fechaHoraHusoGenRegistro)) {
            throw new \InvalidArgumentException('FechaHoraHusoGenRegistro must be in ISO 8601 format with timezone (e.g. 2024-09-13T19:20:30+01:00)');
        }
        $this->FechaHoraHusoGenRegistro = $fechaHoraHusoGenRegistro;
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

    public function getRegistroAlta(): RegistroAlta
    {
        return $this->RegistroAlta;
    }

    public function setRegistroAlta(RegistroAlta $registroAlta): self
    {
        if ($registroAlta !== null && $this->RegistroAnulacion !== null) {
            throw new \InvalidArgumentException('Cannot set both RegistroAlta and RegistroAnulacion');
        }
        $this->RegistroAlta = $registroAlta;
        return $this;
    }

    public function getRegistroAnulacion(): ?RegistroFacturacionAnulacion
    {
        return $this->RegistroAnulacion;
    }

    public function setRegistroAnulacion(?RegistroFacturacionAnulacion $registroAnulacion): self
    {
        if ($registroAnulacion !== null && $this->RegistroAlta !== null) {
            throw new \InvalidArgumentException('Cannot set both RegistroAlta and RegistroAnulacion');
        }
        $this->RegistroAnulacion = $registroAnulacion;
        return $this;
    }
} 