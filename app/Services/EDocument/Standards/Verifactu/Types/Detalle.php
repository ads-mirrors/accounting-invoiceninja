<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Detalle
{
    /** @var string|null */
    #[SerializedName('sum1:Impuesto')]
    protected $Impuesto;

    /** @var string|null */
    #[SerializedName('sum1:ClaveRegimen')]
    protected $ClaveRegimen;

    /** @var string|null */
    #[SerializedName('sum1:CalificacionOperacion')]
    protected $CalificacionOperacion;

    /** @var string|null */
    #[SerializedName('sum1:OperacionExenta')]
    protected $OperacionExenta;

    /** @var float|null */
    #[SerializedName('sum1:TipoImpositivo')]
    protected $TipoImpositivo;

    /** @var float */
    #[SerializedName('sum1:BaseImponibleOimporteNoSujeto')]
    protected $BaseImponibleOimporteNoSujeto;

    /** @var float|null */
    #[SerializedName('sum1:BaseImponibleACoste')]
    protected $BaseImponibleACoste;

    /** @var float|null */
    #[SerializedName('sum1:CuotaRepercutida')]
    protected $CuotaRepercutida;

    /** @var float|null */
    #[SerializedName('sum1:TipoRecargoEquivalencia')]
    protected $TipoRecargoEquivalencia;

    /** @var float|null */
    #[SerializedName('sum1:CuotaRecargoEquivalencia')]
    protected $CuotaRecargoEquivalencia;

    public function getImpuesto(): ?string
    {
        return $this->Impuesto;
    }

    public function setImpuesto(?string $impuesto): self
    {
        $this->Impuesto = $impuesto;
        return $this;
    }

    public function getClaveRegimen(): ?string
    {
        return $this->ClaveRegimen;
    }

    public function setClaveRegimen(?string $claveRegimen): self
    {
        if ($claveRegimen !== null && !preg_match('/^\d{2}$/', $claveRegimen)) {
            throw new \InvalidArgumentException('ClaveRegimen must be a 2-digit number');
        }
        $this->ClaveRegimen = $claveRegimen;
        return $this;
    }

    public function getCalificacionOperacion(): ?string
    {
        return $this->CalificacionOperacion;
    }

    public function setCalificacionOperacion(?string $calificacionOperacion): self
    {
        if ($calificacionOperacion !== null) {
            if (!preg_match('/^[A-Z]\d$/', $calificacionOperacion)) {
                throw new \InvalidArgumentException('CalificacionOperacion must be a letter followed by a digit');
            }
            if ($this->OperacionExenta !== null) {
                throw new \InvalidArgumentException('Cannot set both CalificacionOperacion and OperacionExenta');
            }
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
                throw new \InvalidArgumentException('Cannot set both CalificacionOperacion and OperacionExenta');
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
            $parts = explode('.', (string)$tipoImpositivo);
            $decimalPart = $parts[1] ?? '';

            if (strlen($decimalPart) > 2) {
                throw new \InvalidArgumentException('TipoImpositivo must have at most 2 decimal places');
            }
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
        $parts = explode('.', (string)$baseImponibleOimporteNoSujeto);
        $integerPart = $parts[0];
        $decimalPart = $parts[1] ?? '';

        if (strlen($integerPart) > 12) {
            throw new \InvalidArgumentException('BaseImponibleOimporteNoSujeto must have at most 12 digits before decimal point');
        }
        if (strlen($decimalPart) > 2) {
            throw new \InvalidArgumentException('BaseImponibleOimporteNoSujeto must have at most 2 decimal places');
        }

        $this->BaseImponibleOimporteNoSujeto = $baseImponibleOimporteNoSujeto;
        return $this;
    }

    public function getBaseImponibleACoste(): ?float
    {
        return $this->BaseImponibleACoste;
    }

    public function setBaseImponibleACoste(?float $baseImponibleACoste): self
    {
        if ($baseImponibleACoste !== null) {
            $parts = explode('.', (string)$baseImponibleACoste);
            $integerPart = $parts[0];
            $decimalPart = $parts[1] ?? '';

            if (strlen($integerPart) > 12) {
                throw new \InvalidArgumentException('BaseImponibleACoste must have at most 12 digits before decimal point');
            }
            if (strlen($decimalPart) > 2) {
                throw new \InvalidArgumentException('BaseImponibleACoste must have at most 2 decimal places');
            }
        }
        $this->BaseImponibleACoste = $baseImponibleACoste;
        return $this;
    }

    public function getCuotaRepercutida(): ?float
    {
        return $this->CuotaRepercutida;
    }

    public function setCuotaRepercutida(?float $cuotaRepercutida): self
    {
        if ($cuotaRepercutida !== null) {
            $parts = explode('.', (string)$cuotaRepercutida);
            $integerPart = $parts[0];
            $decimalPart = $parts[1] ?? '';

            if (strlen($integerPart) > 12) {
                throw new \InvalidArgumentException('CuotaRepercutida must have at most 12 digits before decimal point');
            }
            if (strlen($decimalPart) > 2) {
                throw new \InvalidArgumentException('CuotaRepercutida must have at most 2 decimal places');
            }
        }
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
            $parts = explode('.', (string)$tipoRecargoEquivalencia);
            $decimalPart = $parts[1] ?? '';

            if (strlen($decimalPart) > 2) {
                throw new \InvalidArgumentException('TipoRecargoEquivalencia must have at most 2 decimal places');
            }
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
        if ($cuotaRecargoEquivalencia !== null) {
            $parts = explode('.', (string)$cuotaRecargoEquivalencia);
            $integerPart = $parts[0];
            $decimalPart = $parts[1] ?? '';

            if (strlen($integerPart) > 12) {
                throw new \InvalidArgumentException('CuotaRecargoEquivalencia must have at most 12 digits before decimal point');
            }
            if (strlen($decimalPart) > 2) {
                throw new \InvalidArgumentException('CuotaRecargoEquivalencia must have at most 2 decimal places');
            }
        }
        $this->CuotaRecargoEquivalencia = $cuotaRecargoEquivalencia;
        return $this;
    }
} 