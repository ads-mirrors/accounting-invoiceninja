<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class Encadenamiento
{
    /** @var string */
    protected $NumSerieFacturaAnterior;

    /** @var string */
    protected $FechaExpedicionFacturaAnterior;

    public function getNumSerieFacturaAnterior(): string
    {
        return $this->NumSerieFacturaAnterior;
    }

    public function setNumSerieFacturaAnterior(string $numSerieFacturaAnterior): self
    {
        if (strlen($numSerieFacturaAnterior) > 60) {
            throw new \InvalidArgumentException('NumSerieFacturaAnterior must not exceed 60 characters');
        }
        $this->NumSerieFacturaAnterior = $numSerieFacturaAnterior;
        return $this;
    }

    public function getFechaExpedicionFacturaAnterior(): string
    {
        return $this->FechaExpedicionFacturaAnterior;
    }

    public function setFechaExpedicionFacturaAnterior(string $fechaExpedicionFacturaAnterior): self
    {
        // Validate date format DD-MM-YYYY
        if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $fechaExpedicionFacturaAnterior)) {
            throw new \InvalidArgumentException('FechaExpedicionFacturaAnterior must be in DD-MM-YYYY format');
        }
        
        // Validate date components
        list($day, $month, $year) = explode('-', $fechaExpedicionFacturaAnterior);
        if (!checkdate((int)$month, (int)$day, (int)$year)) {
            throw new \InvalidArgumentException('Invalid date');
        }
        
        $this->FechaExpedicionFacturaAnterior = $fechaExpedicionFacturaAnterior;
        return $this;
    }
} 