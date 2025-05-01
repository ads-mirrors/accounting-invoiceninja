<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class DetalleDesglose
{
    /** @var string */
    protected $ClaveRegimen;

    /** @var string */
    protected $CalificacionOperacion;

    /** @var string|null */
    protected $OperacionExenta;

    /** @var float|null */
    protected $TipoImpositivo;

    /** @var float */
    protected $BaseImponibleOimporteNoSujeto;

    /** @var float|null */
    protected $BaseImponibleACoste;

    /** @var float|null */
    protected $CuotaRepercutida;

    /** @var float|null */
    protected $TipoRecargoEquivalencia;

    /** @var float|null */
    protected $CuotaRecargoEquivalencia;

    public function getClaveRegimen(): string
    {
        return $this->ClaveRegimen;
    }

    public function setClaveRegimen(string $claveRegimen): self
    {
        if (!preg_match('/^\d{2}$/', $claveRegimen)) {
            throw new \InvalidArgumentException('ClaveRegimen must be a 2-digit number');
        }
        $this->ClaveRegimen = $claveRegimen;
        return $this;
    }

    public function getCalificacionOperacion(): string
    {
        return $this->CalificacionOperacion;
    }

    public function setCalificacionOperacion(string $calificacionOperacion): self
    {
        if (!preg_match('/^[A-Z]\d$/', $calificacionOperacion)) {
            throw new \InvalidArgumentException('CalificacionOperacion must be a letter followed by a digit');
        }
        if ($this->OperacionExenta !== null) {
            throw new \InvalidArgumentException('Cannot set CalificacionOperacion when OperacionExenta is set');
        }
        $this->CalificacionOperacion = $calificacionOperacion;
        return $this;
    }

    public function getOperacionExenta(): ?string
    {
        return $this->OperacionExenta;
    }

    public function setOperacionExenta(?string $operacionExenta): self
    {
        if ($operacionExenta !== null) {
            if (!preg_match('/^[A-Z]\d$/', $operacionExenta)) {
                throw new \InvalidArgumentException('OperacionExenta must be a letter followed by a digit');
            }
            if ($this->CalificacionOperacion !== null) {
                throw new \InvalidArgumentException('Cannot set OperacionExenta when CalificacionOperacion is set');
            }
        }
        $this->OperacionExenta = $operacionExenta;
        return $this;
    }

    public function getTipoImpositivo(): ?float
    {
        return $this->TipoImpositivo;
    }

    public function setTipoImpositivo(?float $tipoImpositivo): self
    {
        if ($tipoImpositivo !== null) {
            if ($tipoImpositivo < 0 || $tipoImpositivo > 100) {
                throw new \InvalidArgumentException('TipoImpositivo must be between 0 and 100');
            }
        }
        $this->TipoImpositivo = $tipoImpositivo;
        return $this;
    }

    public function getBaseImponibleOimporteNoSujeto(): float
    {
        return $this->BaseImponibleOimporteNoSujeto;
    }

    public function setBaseImponibleOimporteNoSujeto(float $baseImponibleOimporteNoSujeto): self
    {
        $this->BaseImponibleOimporteNoSujeto = $baseImponibleOimporteNoSujeto;
        return $this;
    }

    public function getBaseImponibleACoste(): ?float
    {
        return $this->BaseImponibleACoste;
    }

    public function setBaseImponibleACoste(?float $baseImponibleACoste): self
    {
        $this->BaseImponibleACoste = $baseImponibleACoste;
        return $this;
    }

    public function getCuotaRepercutida(): ?float
    {
        return $this->CuotaRepercutida;
    }

    public function setCuotaRepercutida(?float $cuotaRepercutida): self
    {
        $this->CuotaRepercutida = $cuotaRepercutida;
        return $this;
    }

    public function getTipoRecargoEquivalencia(): ?float
    {
        return $this->TipoRecargoEquivalencia;
    }

    public function setTipoRecargoEquivalencia(?float $tipoRecargoEquivalencia): self
    {
        if ($tipoRecargoEquivalencia !== null) {
            if ($tipoRecargoEquivalencia < 0 || $tipoRecargoEquivalencia > 100) {
                throw new \InvalidArgumentException('TipoRecargoEquivalencia must be between 0 and 100');
            }
        }
        $this->TipoRecargoEquivalencia = $tipoRecargoEquivalencia;
        return $this;
    }

    public function getCuotaRecargoEquivalencia(): ?float
    {
        return $this->CuotaRecargoEquivalencia;
    }

    public function setCuotaRecargoEquivalencia(?float $cuotaRecargoEquivalencia): self
    {
        $this->CuotaRecargoEquivalencia = $cuotaRecargoEquivalencia;
        return $this;
    }
} 