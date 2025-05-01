<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class DesgloseRectificacion
{
    /** @var float */
    protected $baseRectificada;

    /** @var float */
    protected $cuotaRectificada;

    /** @var float|null */
    protected $cuotaRecargoRectificada;

    public function getBaseRectificada(): float
    {
        return $this->baseRectificada;
    }

    public function setBaseRectificada(float $baseRectificada): self
    {
        // Validate format: max 12 digits before decimal point, 2 after
        $strValue = (string)$baseRectificada;
        if (strlen(substr(strrchr($strValue, "."), 1)) > 2) {
            throw new \InvalidArgumentException('BaseRectificada must have at most 2 decimal places');
        }
        if (strlen(explode('.', $strValue)[0]) > 12) {
            throw new \InvalidArgumentException('BaseRectificada must have at most 12 digits before decimal point');
        }
        $this->baseRectificada = $baseRectificada;
        return $this;
    }

    public function getCuotaRectificada(): float
    {
        return $this->cuotaRectificada;
    }

    public function setCuotaRectificada(float $cuotaRectificada): self
    {
        // Validate format: max 12 digits before decimal point, 2 after
        $strValue = (string)$cuotaRectificada;
        if (strlen(substr(strrchr($strValue, "."), 1)) > 2) {
            throw new \InvalidArgumentException('CuotaRectificada must have at most 2 decimal places');
        }
        if (strlen(explode('.', $strValue)[0]) > 12) {
            throw new \InvalidArgumentException('CuotaRectificada must have at most 12 digits before decimal point');
        }
        $this->cuotaRectificada = $cuotaRectificada;
        return $this;
    }

    public function getCuotaRecargoRectificada(): ?float
    {
        return $this->cuotaRecargoRectificada;
    }

    public function setCuotaRecargoRectificada(?float $cuotaRecargoRectificada): self
    {
        if ($cuotaRecargoRectificada !== null) {
            // Validate format: max 12 digits before decimal point, 2 after
            $strValue = (string)$cuotaRecargoRectificada;
            if (strlen(substr(strrchr($strValue, "."), 1)) > 2) {
                throw new \InvalidArgumentException('CuotaRecargoRectificada must have at most 2 decimal places');
            }
            if (strlen(explode('.', $strValue)[0]) > 12) {
                throw new \InvalidArgumentException('CuotaRecargoRectificada must have at most 12 digits before decimal point');
            }
        }
        $this->cuotaRecargoRectificada = $cuotaRecargoRectificada;
        return $this;
    }
} 