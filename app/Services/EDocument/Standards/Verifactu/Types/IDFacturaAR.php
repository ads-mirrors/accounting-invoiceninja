<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class IDFacturaAR
{
    /** @var string NIF format */
    protected $idEmisorFactura;

    /** @var string */
    protected $numSerieFactura;

    /** @var string Date format YYYY-MM-DD */
    protected $fechaExpedicionFactura;

    /** @var string|null */
    protected $numRegistroAcuerdoFacturacion;

    public function getIdEmisorFactura(): string
    {
        return $this->idEmisorFactura;
    }

    public function setIdEmisorFactura(string $idEmisorFactura): self
    {
        // TODO: Add NIF validation
        $this->idEmisorFactura = $idEmisorFactura;
        return $this;
    }

    public function getNumSerieFactura(): string
    {
        return $this->numSerieFactura;
    }

    public function setNumSerieFactura(string $numSerieFactura): self
    {
        $this->numSerieFactura = $numSerieFactura;
        return $this;
    }

    public function getFechaExpedicionFactura(): string
    {
        return $this->fechaExpedicionFactura;
    }

    public function setFechaExpedicionFactura(string $fechaExpedicionFactura): self
    {
        if (!\DateTime::createFromFormat('Y-m-d', $fechaExpedicionFactura)) {
            throw new \InvalidArgumentException('FechaExpedicionFactura must be in YYYY-MM-DD format');
        }
        $this->fechaExpedicionFactura = $fechaExpedicionFactura;
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
} 