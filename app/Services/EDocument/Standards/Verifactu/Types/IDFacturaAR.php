<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class IDFacturaAR
{
    /** @var string */
    protected $idEmisorFactura;

    /** @var string */
    protected $numSerieFactura;

    /** @var string */
    protected $fechaExpedicionFactura;

    /** @var string|null */
    protected $numSerieFacturaOrigen;

    /** @var string|null */
    protected $fechaExpedicionFacturaOrigen;

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
        if (strlen($numSerieFactura) > 60) {
            throw new \InvalidArgumentException('NumSerieFactura must not exceed 60 characters');
        }
        $this->numSerieFactura = $numSerieFactura;
        return $this;
    }

    public function getFechaExpedicionFactura(): string
    {
        return $this->fechaExpedicionFactura;
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
        
        $this->fechaExpedicionFactura = $fechaExpedicionFactura;
        return $this;
    }

    public function getNumSerieFacturaOrigen(): ?string
    {
        return $this->numSerieFacturaOrigen;
    }

    public function setNumSerieFacturaOrigen(?string $numSerieFacturaOrigen): self
    {
        if ($numSerieFacturaOrigen !== null && strlen($numSerieFacturaOrigen) > 60) {
            throw new \InvalidArgumentException('NumSerieFacturaOrigen must not exceed 60 characters');
        }
        $this->numSerieFacturaOrigen = $numSerieFacturaOrigen;
        return $this;
    }

    public function getFechaExpedicionFacturaOrigen(): ?string
    {
        return $this->fechaExpedicionFacturaOrigen;
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
        
        $this->fechaExpedicionFacturaOrigen = $fechaExpedicionFacturaOrigen;
        return $this;
    }
} 