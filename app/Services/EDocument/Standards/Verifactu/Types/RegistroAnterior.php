<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class RegistroAnterior
{
    /** @var string */
    #[SerializedName('sum1:NumRegistroAcuerdoFacturacion')]
    protected $NumRegistroAcuerdoFacturacion;

    /** @var string */
    #[SerializedName('sum1:FechaHoraHusoGenRegistro')]
    protected $FechaHoraHusoGenRegistro;

    /** @var string */
    #[SerializedName('sum1:Huella')]
    protected $Huella;

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

    public function getFechaHoraHusoGenRegistro(): string
    {
        return $this->FechaHoraHusoGenRegistro;
    }

    public function setFechaHoraHusoGenRegistro(string $fechaHoraHusoGenRegistro): self
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $fechaHoraHusoGenRegistro)) {
            throw new \InvalidArgumentException('FechaHoraHusoGenRegistro must be in ISO 8601 format with timezone (e.g. 2024-09-13T19:20:30+01:00)');
        }
        $this->FechaHoraHusoGenRegistro = $fechaHoraHusoGenRegistro;
        return $this;
    }

    public function getHuella(): string
    {
        return $this->Huella;
    }

    public function setHuella(string $huella): self
    {
        if (strlen($huella) > 64) {
            throw new \InvalidArgumentException('Huella must not exceed 64 characters');
        }
        $this->Huella = $huella;
        return $this;
    }
} 