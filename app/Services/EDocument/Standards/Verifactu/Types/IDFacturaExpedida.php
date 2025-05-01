<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class IDFacturaExpedida
{
    /** @var string NIF format */
    protected $IDEmisorFactura;

    /** @var string */
    protected $NumSerieFactura;

    /** @var string Date format YYYY-MM-DD */
    protected $FechaExpedicionFactura;

    public function getIDEmisorFactura(): string
    {
        return $this->IDEmisorFactura;
    }

    public function setIDEmisorFactura(string $idEmisorFactura): self
    {
        // TODO: Add NIF validation
        $this->IDEmisorFactura = $idEmisorFactura;
        return $this;
    }

    public function getNumSerieFactura(): string
    {
        return $this->NumSerieFactura;
    }

    public function setNumSerieFactura(string $numSerieFactura): self
    {
        $this->NumSerieFactura = $numSerieFactura;
        return $this;
    }

    public function getFechaExpedicionFactura(): string
    {
        return $this->FechaExpedicionFactura;
    }

    public function setFechaExpedicionFactura(string $fechaExpedicionFactura): self
    {
        // Validate date format
        if (!\DateTime::createFromFormat('Y-m-d', $fechaExpedicionFactura)) {
            throw new \InvalidArgumentException('FechaExpedicionFactura must be in YYYY-MM-DD format');
        }
        $this->FechaExpedicionFactura = $fechaExpedicionFactura;
        return $this;
    }
} 