<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class RegistroAlta
{
    /** @var string */
    protected $IDVersion;

    /** @var IDFactura */
    protected $IDFactura;

    /** @var string */
    protected $NombreRazonEmisor;

    /** @var string */
    protected $TipoFactura;

    /** @var string */
    protected $DescripcionOperacion;

    /** @var Destinatarios */
    protected $Destinatarios;

    /** @var Desglose */
    protected $Desglose;

    /** @var float */
    protected $CuotaTotal;

    /** @var float */
    protected $ImporteTotal;

    /** @var Encadenamiento|null */
    protected $Encadenamiento;

    /** @var SistemaInformatico */
    protected $SistemaInformatico;

    /** @var string */
    protected $FechaHoraHusoGenRegistro;

    /** @var string */
    protected $TipoHuella;

    /** @var string */
    protected $Huella;

    public function getIDVersion(): string
    {
        return $this->IDVersion;
    }

    public function setIDVersion(string $idVersion): self
    {
        $this->IDVersion = $idVersion;
        return $this;
    }

    public function getIDFactura(): IDFactura
    {
        return $this->IDFactura;
    }

    public function setIDFactura(IDFactura $idFactura): self
    {
        $this->IDFactura = $idFactura;
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

    public function getTipoFactura(): string
    {
        return $this->TipoFactura;
    }

    public function setTipoFactura(string $tipoFactura): self
    {
        if (!preg_match('/^F[1-4]$/', $tipoFactura)) {
            throw new \InvalidArgumentException('TipoFactura must be F1, F2, F3, or F4');
        }
        $this->TipoFactura = $tipoFactura;
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

    public function getDestinatarios(): Destinatarios
    {
        return $this->Destinatarios;
    }

    public function setDestinatarios(Destinatarios $destinatarios): self
    {
        $this->Destinatarios = $destinatarios;
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
        $this->CuotaTotal = $cuotaTotal;
        return $this;
    }

    public function getImporteTotal(): float
    {
        return $this->ImporteTotal;
    }

    public function setImporteTotal(float $importeTotal): self
    {
        $this->ImporteTotal = $importeTotal;
        return $this;
    }

    public function getEncadenamiento(): ?Encadenamiento
    {
        return $this->Encadenamiento;
    }

    public function setEncadenamiento(?Encadenamiento $encadenamiento): self
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

    public function getFechaHoraHusoGenRegistro(): string
    {
        return $this->FechaHoraHusoGenRegistro;
    }

    public function setFechaHoraHusoGenRegistro(string $fechaHoraHusoGenRegistro): self
    {
        // Validate ISO 8601 date format with timezone
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $fechaHoraHusoGenRegistro)) {
            throw new \InvalidArgumentException('FechaHoraHusoGenRegistro must be in ISO 8601 format (YYYY-MM-DDThh:mm:ss±hh:mm)');
        }
        $this->FechaHoraHusoGenRegistro = $fechaHoraHusoGenRegistro;
        return $this;
    }

    public function getTipoHuella(): string
    {
        return $this->TipoHuella;
    }

    public function setTipoHuella(string $tipoHuella): self
    {
        if (!preg_match('/^\d{2}$/', $tipoHuella)) {
            throw new \InvalidArgumentException('TipoHuella must be a 2-digit number');
        }
        $this->TipoHuella = $tipoHuella;
        return $this;
    }

    public function getHuella(): string
    {
        return $this->Huella;
    }

    public function setHuella(string $huella): self
    {
        $this->Huella = $huella;
        return $this;
    }
} 