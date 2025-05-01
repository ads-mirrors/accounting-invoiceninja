<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class IDFacturaAR
{
    /** @var string */
    protected $IDEmisorFactura;

    /** @var string */
    protected $NumSerieFactura;

    /** @var string */
    protected $FechaExpedicionFactura;

    /** @var string|null */
    protected $NumSerieFacturaOrigen;

    /** @var string|null */
    protected $FechaExpedicionFacturaOrigen;

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
        if (strlen($numSerieFactura) > 60) {
            throw new \InvalidArgumentException('NumSerieFactura must not exceed 60 characters');
        }
        $this->NumSerieFactura = $numSerieFactura;
        return $this;
    }

    public function getFechaExpedicionFactura(): string
    {
        return $this->FechaExpedicionFactura;
    }

    public function setFechaExpedicionFactura(string $fechaExpedicionFactura): self
    {
        // Validate date format DD-MM-YYYY
        if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $fechaExpedicionFactura)) {
            throw new \InvalidArgumentException('FechaExpedicionFactura must be in DD-MM-YYYY format');
        }
        
        // Validate date components
        list($day, $month, $year) = explode('-', $fechaExpedicionFactura);
        if (!checkdate((int)$month, (int)$day, (int)$year)) {
            throw new \InvalidArgumentException('Invalid date');
        }
        
        $this->FechaExpedicionFactura = $fechaExpedicionFactura;
        return $this;
    }

    public function getNumSerieFacturaOrigen(): ?string
    {
        return $this->NumSerieFacturaOrigen;
    }

    public function setNumSerieFacturaOrigen(?string $numSerieFacturaOrigen): self
    {
        if ($numSerieFacturaOrigen !== null && strlen($numSerieFacturaOrigen) > 60) {
            throw new \InvalidArgumentException('NumSerieFacturaOrigen must not exceed 60 characters');
        }
        $this->NumSerieFacturaOrigen = $numSerieFacturaOrigen;
        return $this;
    }

    public function getFechaExpedicionFacturaOrigen(): ?string
    {
        return $this->FechaExpedicionFacturaOrigen;
    }

    public function setFechaExpedicionFacturaOrigen(?string $fechaExpedicionFacturaOrigen): self
    {
        if ($fechaExpedicionFacturaOrigen !== null) {
            // Validate date format DD-MM-YYYY
            if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $fechaExpedicionFacturaOrigen)) {
                throw new \InvalidArgumentException('FechaExpedicionFacturaOrigen must be in DD-MM-YYYY format');
            }
            
            // Validate date components
            list($day, $month, $year) = explode('-', $fechaExpedicionFacturaOrigen);
            if (!checkdate((int)$month, (int)$day, (int)$year)) {
                throw new \InvalidArgumentException('Invalid date');
            }
        }
        
        $this->FechaExpedicionFacturaOrigen = $fechaExpedicionFacturaOrigen;
        return $this;
    }
} 