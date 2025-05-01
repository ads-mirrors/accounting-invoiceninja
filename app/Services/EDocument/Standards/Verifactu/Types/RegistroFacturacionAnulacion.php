<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class RegistroFacturacionAnulacion
{
    /** @var string */
    protected $idVersion;

    /** @var IDFacturaAR */
    protected $idFactura;

    /** @var string|null Max length 70 characters */
    protected $refExterna;

    /** @var string Max length 120 characters */
    protected $nombreRazonEmisor;

    /** @var string|null Max length 2000 characters */
    protected $motivoAnulacion;

    /** @var SistemaInformatico */
    protected $sistemaInformatico;

    /** @var string */
    protected $huella;

    /** @var string|null */
    protected $signature;

    /** @var string */
    protected $fechaHoraHusoGenRegistro;

    /** @var string|null Max length 15 characters */
    protected $numRegistroAcuerdoFacturacion;

    /** @var string|null Max length 16 characters */
    protected $idAcuerdoSistemaInformatico;

    /** @var string */
    protected $tipoHuella;

    public function getIdVersion(): string
    {
        return $this->idVersion;
    }

    public function setIdVersion(string $idVersion): self
    {
        $this->idVersion = $idVersion;
        return $this;
    }

    public function getIdFactura(): IDFacturaAR
    {
        return $this->idFactura;
    }

    public function setIdFactura(IDFacturaAR $idFactura): self
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

    public function getMotivoAnulacion(): ?string
    {
        return $this->motivoAnulacion;
    }

    public function setMotivoAnulacion(?string $motivoAnulacion): self
    {
        if ($motivoAnulacion !== null && strlen($motivoAnulacion) > 2000) {
            throw new \InvalidArgumentException('MotivoAnulacion must not exceed 2000 characters');
        }
        $this->motivoAnulacion = $motivoAnulacion;
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

    public function getHuella(): string
    {
        return $this->huella;
    }

    public function setHuella(string $huella): self
    {
        if (strlen($huella) > 100) {
            throw new \InvalidArgumentException('Huella must not exceed 100 characters');
        }
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

    public function getFechaHoraHusoGenRegistro(): string
    {
        return $this->fechaHoraHusoGenRegistro;
    }

    public function setFechaHoraHusoGenRegistro(string $fechaHoraHusoGenRegistro): self
    {
        // Validate ISO 8601 format with timezone
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $fechaHoraHusoGenRegistro)) {
            throw new \InvalidArgumentException('FechaHoraHusoGenRegistro must be in ISO 8601 format with timezone (e.g. 2024-09-13T19:20:30+01:00)');
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
        if ($numRegistroAcuerdoFacturacion !== null && strlen($numRegistroAcuerdoFacturacion) > 15) {
            throw new \InvalidArgumentException('NumRegistroAcuerdoFacturacion must not exceed 15 characters');
        }
        $this->numRegistroAcuerdoFacturacion = $numRegistroAcuerdoFacturacion;
        return $this;
    }

    public function getIdAcuerdoSistemaInformatico(): ?string
    {
        return $this->idAcuerdoSistemaInformatico;
    }

    public function setIdAcuerdoSistemaInformatico(?string $idAcuerdoSistemaInformatico): self
    {
        if ($idAcuerdoSistemaInformatico !== null && strlen($idAcuerdoSistemaInformatico) > 16) {
            throw new \InvalidArgumentException('IdAcuerdoSistemaInformatico must not exceed 16 characters');
        }
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
} 