<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class RegistroFacturacionAnulacion
{
    /** @var string */
    protected $IDVersion;

    /** @var IDFacturaAR */
    protected $IDFactura;

    /** @var string|null Max length 70 characters */
    protected $RefExterna;

    /** @var string Max length 120 characters */
    protected $NombreRazonEmisor;

    /** @var string|null Max length 2000 characters */
    protected $MotivoAnulacion;

    /** @var SistemaInformatico */
    protected $SistemaInformatico;

    /** @var string */
    protected $Huella;

    /** @var string|null */
    protected $Signature;

    /** @var string */
    protected $FechaHoraHusoGenRegistro;

    /** @var string|null Max length 15 characters */
    protected $NumRegistroAcuerdoFacturacion;

    /** @var string|null Max length 16 characters */
    protected $IDAcuerdoSistemaInformatico;

    /** @var string */
    protected $TipoHuella;

    public function getIDVersion(): string
    {
        return $this->IDVersion;
    }

    public function setIDVersion(string $idVersion): self
    {
        $this->IDVersion = $idVersion;
        return $this;
    }

    public function getIDFactura(): IDFacturaAR
    {
        return $this->IDFactura;
    }

    public function setIDFactura(IDFacturaAR $idFactura): self
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

    public function getMotivoAnulacion(): ?string
    {
        return $this->MotivoAnulacion;
    }

    public function setMotivoAnulacion(?string $motivoAnulacion): self
    {
        if ($motivoAnulacion !== null && strlen($motivoAnulacion) > 2000) {
            throw new \InvalidArgumentException('MotivoAnulacion must not exceed 2000 characters');
        }
        $this->MotivoAnulacion = $motivoAnulacion;
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

    public function getHuella(): string
    {
        return $this->Huella;
    }

    public function setHuella(string $huella): self
    {
        if (strlen($huella) > 100) {
            throw new \InvalidArgumentException('Huella must not exceed 100 characters');
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

    public function getFechaHoraHusoGenRegistro(): string
    {
        return $this->FechaHoraHusoGenRegistro;
    }

    public function setFechaHoraHusoGenRegistro(string $fechaHoraHusoGenRegistro): self
    {
        // Validate ISO 8601 format with timezone
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $fechaHoraHusoGenRegistro)) {
            throw new \InvalidArgumentException('FechaHoraHusoGenRegistro must be in ISO 8601 format with timezone (e.g. 2024-09-13T19:20:30+01:00)');
        }
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
            throw new \InvalidArgumentException('IdAcuerdoSistemaInformatico must not exceed 16 characters');
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
        $this->TipoHuella = $tipoHuella;
        return $this;
    }
} 