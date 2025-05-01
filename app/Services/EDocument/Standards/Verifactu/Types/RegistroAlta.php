<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class RegistroAlta
{
    /** @var string */
    protected $idVersion;

    /** @var IDFactura */
    protected $idFactura;

    /** @var string */
    protected $nombreRazonEmisor;

    /** @var string */
    protected $tipoFactura;

    /** @var string */
    protected $descripcionOperacion;

    /** @var array<IDDestinatario> */
    protected $destinatarios = [];

    /** @var array<DetalleDesglose> */
    protected $desglose = [];

    /** @var float */
    protected $cuotaTotal;

    /** @var float */
    protected $importeTotal;

    /** @var RegistroAnterior|null */
    protected $encadenamiento;

    /** @var SistemaInformatico */
    protected $sistemaInformatico;

    /** @var string */
    protected $fechaHoraHusoGenRegistro;

    /** @var string */
    protected $tipoHuella;

    /** @var string */
    protected $huella;

    public function getIdVersion(): string
    {
        return $this->idVersion;
    }

    public function setIdVersion(string $idVersion): self
    {
        $this->idVersion = $idVersion;
        return $this;
    }

    public function getIdFactura(): IDFactura
    {
        return $this->idFactura;
    }

    public function setIdFactura(IDFactura $idFactura): self
    {
        $this->idFactura = $idFactura;
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

    public function getTipoFactura(): string
    {
        return $this->tipoFactura;
    }

    public function setTipoFactura(string $tipoFactura): self
    {
        if (!in_array($tipoFactura, ['F1', 'F2', 'F3', 'F4', 'R1', 'R2', 'R3', 'R4'])) {
            throw new \InvalidArgumentException('Invalid TipoFactura value');
        }
        $this->tipoFactura = $tipoFactura;
        return $this;
    }

    public function getDescripcionOperacion(): string
    {
        return $this->descripcionOperacion;
    }

    public function setDescripcionOperacion(string $descripcionOperacion): self
    {
        if (strlen($descripcionOperacion) > 500) {
            throw new \InvalidArgumentException('DescripcionOperacion must not exceed 500 characters');
        }
        $this->descripcionOperacion = $descripcionOperacion;
        return $this;
    }

    /**
     * @return array<IDDestinatario>
     */
    public function getDestinatarios(): array
    {
        return $this->destinatarios;
    }

    public function addDestinatario(IDDestinatario $destinatario): self
    {
        $this->destinatarios[] = $destinatario;
        return $this;
    }

    /**
     * @return array<DetalleDesglose>
     */
    public function getDesglose(): array
    {
        return $this->desglose;
    }

    public function addDesglose(DetalleDesglose $detalle): self
    {
        $this->desglose[] = $detalle;
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

    public function getEncadenamiento(): ?RegistroAnterior
    {
        return $this->encadenamiento;
    }

    public function setEncadenamiento(?RegistroAnterior $encadenamiento): self
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
        // Validate ISO 8601 format with timezone
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $fechaHoraHusoGenRegistro)) {
            throw new \InvalidArgumentException('FechaHoraHusoGenRegistro must be in ISO 8601 format with timezone (e.g. 2024-09-13T19:20:30+01:00)');
        }
        $this->fechaHoraHusoGenRegistro = $fechaHoraHusoGenRegistro;
        return $this;
    }

    public function getTipoHuella(): string
    {
        return $this->tipoHuella;
    }

    public function setTipoHuella(string $tipoHuella): self
    {
        if (!in_array($tipoHuella, ['01', '02', '03', '04'])) {
            throw new \InvalidArgumentException('Invalid TipoHuella value');
        }
        $this->tipoHuella = $tipoHuella;
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
} 