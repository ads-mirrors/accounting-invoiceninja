<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class RechazoPrevio
{
    /** @var string */
    #[SerializedName('sum1:NumRegistroAcuerdoFacturacion')]
    protected $NumRegistroAcuerdoFacturacion;

    /** @var string */
    #[SerializedName('sum1:FechaRegistroAcuerdoFacturacion')]
    protected $FechaRegistroAcuerdoFacturacion;

    /** @var string */
    #[SerializedName('sum1:MotivoRechazo')]
    protected $MotivoRechazo;

    public function getNumRegistroAcuerdoFacturacion(): string
    {
        return $this->NumRegistroAcuerdoFacturacion;
    }

    public function setNumRegistroAcuerdoFacturacion(string $numRegistroAcuerdoFacturacion): self
    {
        if (strlen($numRegistroAcuerdoFacturacion) > 15) {
            throw new \InvalidArgumentException('NumRegistroAcuerdoFacturacion must not exceed 15 characters');
        }
        $this->NumRegistroAcuerdoFacturacion = $numRegistroAcuerdoFacturacion;
        return $this;
    }

    public function getFechaRegistroAcuerdoFacturacion(): string
    {
        return $this->FechaRegistroAcuerdoFacturacion;
    }

    public function setFechaRegistroAcuerdoFacturacion(string $fechaRegistroAcuerdoFacturacion): self
    {
        // Validate date format YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaRegistroAcuerdoFacturacion)) {
            throw new \InvalidArgumentException('FechaRegistroAcuerdoFacturacion must be in YYYY-MM-DD format');
        }
        
        // Validate date components
        list($year, $month, $day) = explode('-', $fechaRegistroAcuerdoFacturacion);
        if (!checkdate((int)$month, (int)$day, (int)$year)) {
            throw new \InvalidArgumentException('Invalid date');
        }
        
        $this->FechaRegistroAcuerdoFacturacion = $fechaRegistroAcuerdoFacturacion;
        return $this;
    }

    public function getMotivoRechazo(): string
    {
        return $this->MotivoRechazo;
    }

    public function setMotivoRechazo(string $motivoRechazo): self
    {
        if (strlen($motivoRechazo) > 2000) {
            throw new \InvalidArgumentException('MotivoRechazo must not exceed 2000 characters');
        }
        $this->MotivoRechazo = $motivoRechazo;
        return $this;
    }
} 