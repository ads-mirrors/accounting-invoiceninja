<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class DesgloseRectificacion
{
    /** @var float */
    #[SerializedName('sum1:BaseRectificada')]
    protected $BaseRectificada;

    /** @var float */
    #[SerializedName('sum1:CuotaRectificada')]
    protected $CuotaRectificada;

    /** @var float|null */
    #[SerializedName('sum1:CuotaRecargoRectificada')]
    protected $CuotaRecargoRectificada;

    public function getBaseRectificada(): float
    {
        return $this->BaseRectificada;
    }

    public function setBaseRectificada(float $baseRectificada): self
    {
        // Validate format: max 12 digits before decimal point, 2 after
        $parts = explode('.', (string)$baseRectificada);
        $integerPart = $parts[0];
        $decimalPart = $parts[1] ?? '';

        if (strlen($integerPart) > 12) {
            throw new \InvalidArgumentException('BaseRectificada must have at most 12 digits before decimal point');
        }
        if (strlen($decimalPart) > 2) {
            throw new \InvalidArgumentException('BaseRectificada must have at most 2 decimal places');
        }

        $this->BaseRectificada = $baseRectificada;
        return $this;
    }

    public function getCuotaRectificada(): float
    {
        return $this->CuotaRectificada;
    }

    public function setCuotaRectificada(float $cuotaRectificada): self
    {
        // Validate format: max 12 digits before decimal point, 2 after
        $parts = explode('.', (string)$cuotaRectificada);
        $integerPart = $parts[0];
        $decimalPart = $parts[1] ?? '';

        if (strlen($integerPart) > 12) {
            throw new \InvalidArgumentException('CuotaRectificada must have at most 12 digits before decimal point');
        }
        if (strlen($decimalPart) > 2) {
            throw new \InvalidArgumentException('CuotaRectificada must have at most 2 decimal places');
        }

        $this->CuotaRectificada = $cuotaRectificada;
        return $this;
    }

    public function getCuotaRecargoRectificada(): ?float
    {
        return $this->CuotaRecargoRectificada;
    }

    public function setCuotaRecargoRectificada(?float $cuotaRecargoRectificada): self
    {
        if ($cuotaRecargoRectificada !== null) {
            // Validate format: max 12 digits before decimal point, 2 after
            $parts = explode('.', (string)$cuotaRecargoRectificada);
            $integerPart = $parts[0];
            $decimalPart = $parts[1] ?? '';

            if (strlen($integerPart) > 12) {
                throw new \InvalidArgumentException('CuotaRecargoRectificada must have at most 12 digits before decimal point');
            }
            if (strlen($decimalPart) > 2) {
                throw new \InvalidArgumentException('CuotaRecargoRectificada must have at most 2 decimal places');
            }
        }

        $this->CuotaRecargoRectificada = $cuotaRecargoRectificada;
        return $this;
    }
} 