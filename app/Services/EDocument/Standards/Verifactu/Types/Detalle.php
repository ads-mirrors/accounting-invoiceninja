<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class Detalle
{
    /** @var string|null */
    protected $impuesto;

    /** @var string|null */
    protected $claveRegimen;

    /** @var string|null */
    protected $calificacionOperacion;

    /** @var string|null */
    protected $operacionExenta;

    /** @var float|null */
    protected $tipoImpositivo;

    /** @var float */
    protected $baseImponibleOimporteNoSujeto;

    /** @var float|null */
    protected $baseImponibleACoste;

    /** @var float|null */
    protected $cuotaRepercutida;

    /** @var float|null */
    protected $tipoRecargoEquivalencia;

    /** @var float|null */
    protected $cuotaRecargoEquivalencia;

    public function getImpuesto(): ?string
    {
        return $this->impuesto;
    }

    public function setImpuesto(?string $impuesto): self
    {
        $this->impuesto = $impuesto;
        return $this;
    }

    public function getClaveRegimen(): ?string
    {
        return $this->claveRegimen;
    }

    public function setClaveRegimen(?string $claveRegimen): self
    {
        $this->claveRegimen = $claveRegimen;
        return $this;
    }

    public function getCalificacionOperacion(): ?string
    {
        return $this->calificacionOperacion;
    }

    public function setCalificacionOperacion(?string $calificacionOperacion): self
    {
        if ($calificacionOperacion !== null && $this->operacionExenta !== null) {
            throw new \InvalidArgumentException('Cannot set both CalificacionOperacion and OperacionExenta');
        }
        $this->calificacionOperacion = $calificacionOperacion;
        return $this;
    }

    public function getOperacionExenta(): ?string
    {
        return $this->operacionExenta;
    }

    public function setOperacionExenta(?string $operacionExenta): self
    {
        if ($operacionExenta !== null && $this->calificacionOperacion !== null) {
            throw new \InvalidArgumentException('Cannot set both CalificacionOperacion and OperacionExenta');
        }
        $this->operacionExenta = $operacionExenta;
        return $this;
    }

    public function getTipoImpositivo(): ?float
    {
        return $this->tipoImpositivo;
    }

    public function setTipoImpositivo(?float $tipoImpositivo): self
    {
        if ($tipoImpositivo !== null) {
            // Validate format: max 2 decimal places
            if (strlen(substr(strrchr((string)$tipoImpositivo, "."), 1)) > 2) {
                throw new \InvalidArgumentException('TipoImpositivo must have at most 2 decimal places');
            }
        }
        $this->tipoImpositivo = $tipoImpositivo;
        return $this;
    }

    public function getBaseImponibleOimporteNoSujeto(): float
    {
        return $this->baseImponibleOimporteNoSujeto;
    }

    public function setBaseImponibleOimporteNoSujeto(float $baseImponibleOimporteNoSujeto): self
    {
        // Validate format: max 12 digits before decimal point, 2 after
        if (strlen(substr(strrchr((string)$baseImponibleOimporteNoSujeto, "."), 1)) > 2) {
            throw new \InvalidArgumentException('BaseImponibleOimporteNoSujeto must have at most 2 decimal places');
        }
        if (strlen(explode('.', (string)$baseImponibleOimporteNoSujeto)[0]) > 12) {
            throw new \InvalidArgumentException('BaseImponibleOimporteNoSujeto must have at most 12 digits before decimal point');
        }
        $this->baseImponibleOimporteNoSujeto = $baseImponibleOimporteNoSujeto;
        return $this;
    }

    public function getBaseImponibleACoste(): ?float
    {
        return $this->baseImponibleACoste;
    }

    public function setBaseImponibleACoste(?float $baseImponibleACoste): self
    {
        if ($baseImponibleACoste !== null) {
            // Validate format: max 12 digits before decimal point, 2 after
            if (strlen(substr(strrchr((string)$baseImponibleACoste, "."), 1)) > 2) {
                throw new \InvalidArgumentException('BaseImponibleACoste must have at most 2 decimal places');
            }
            if (strlen(explode('.', (string)$baseImponibleACoste)[0]) > 12) {
                throw new \InvalidArgumentException('BaseImponibleACoste must have at most 12 digits before decimal point');
            }
        }
        $this->baseImponibleACoste = $baseImponibleACoste;
        return $this;
    }

    public function getCuotaRepercutida(): ?float
    {
        return $this->cuotaRepercutida;
    }

    public function setCuotaRepercutida(?float $cuotaRepercutida): self
    {
        if ($cuotaRepercutida !== null) {
            // Validate format: max 12 digits before decimal point, 2 after
            if (strlen(substr(strrchr((string)$cuotaRepercutida, "."), 1)) > 2) {
                throw new \InvalidArgumentException('CuotaRepercutida must have at most 2 decimal places');
            }
            if (strlen(explode('.', (string)$cuotaRepercutida)[0]) > 12) {
                throw new \InvalidArgumentException('CuotaRepercutida must have at most 12 digits before decimal point');
            }
        }
        $this->cuotaRepercutida = $cuotaRepercutida;
        return $this;
    }

    public function getTipoRecargoEquivalencia(): ?float
    {
        return $this->tipoRecargoEquivalencia;
    }

    public function setTipoRecargoEquivalencia(?float $tipoRecargoEquivalencia): self
    {
        if ($tipoRecargoEquivalencia !== null) {
            // Validate format: max 2 decimal places
            if (strlen(substr(strrchr((string)$tipoRecargoEquivalencia, "."), 1)) > 2) {
                throw new \InvalidArgumentException('TipoRecargoEquivalencia must have at most 2 decimal places');
            }
        }
        $this->tipoRecargoEquivalencia = $tipoRecargoEquivalencia;
        return $this;
    }

    public function getCuotaRecargoEquivalencia(): ?float
    {
        return $this->cuotaRecargoEquivalencia;
    }

    public function setCuotaRecargoEquivalencia(?float $cuotaRecargoEquivalencia): self
    {
        if ($cuotaRecargoEquivalencia !== null) {
            // Validate format: max 12 digits before decimal point, 2 after
            if (strlen(substr(strrchr((string)$cuotaRecargoEquivalencia, "."), 1)) > 2) {
                throw new \InvalidArgumentException('CuotaRecargoEquivalencia must have at most 2 decimal places');
            }
            if (strlen(explode('.', (string)$cuotaRecargoEquivalencia)[0]) > 12) {
                throw new \InvalidArgumentException('CuotaRecargoEquivalencia must have at most 12 digits before decimal point');
            }
        }
        $this->cuotaRecargoEquivalencia = $cuotaRecargoEquivalencia;
        return $this;
    }
} 