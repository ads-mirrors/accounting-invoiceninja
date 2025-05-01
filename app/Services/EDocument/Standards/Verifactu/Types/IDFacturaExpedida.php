<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class IDFacturaExpedida
{
    /** @var string NIF format */
    protected $idEmisorFactura;

    /** @var string */
    protected $numSerieFactura;

    /** @var string Date format YYYY-MM-DD */
    protected $fechaExpedicionFactura;

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
        // Validate date format
        if (!\DateTime::createFromFormat('Y-m-d', $fechaExpedicionFactura)) {
            throw new \InvalidArgumentException('FechaExpedicionFactura must be in YYYY-MM-DD format');
        }
        $this->fechaExpedicionFactura = $fechaExpedicionFactura;
        return $this;
    }
} 