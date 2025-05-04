<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class OperacionExenta
{
    public const E1 = 'E1'; // EXENTA por Art. 20
    public const E2 = 'E2'; // EXENTA por Art. 21
    public const E3 = 'E3'; // EXENTA por Art. 22
    public const E4 = 'E4'; // EXENTA por Art. 24
    public const E5 = 'E5'; // EXENTA por Art. 25
    public const E6 = 'E6'; // EXENTA por otros

    /** @var string */
    #[SerializedName('sum1:Value')]
    protected $Value;

    /** @var string */
    #[SerializedName('sum1:CausaExencion')]
    protected $CausaExencion;

    /** @var float */
    #[SerializedName('sum1:BaseImponible')]
    protected $BaseImponible;

    public function __construct(string $value)
    {
        $this->setValue($value);
    }

    public function getValue(): string
    {
        return $this->Value;
    }

    public function setValue(string $value): self
    {
        $validValues = [
            self::E1,
            self::E2,
            self::E3,
            self::E4,
            self::E5,
            self::E6,
        ];

        if (!in_array($value, $validValues)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid OperacionExenta value. Must be one of: %s',
                implode(', ', $validValues)
            ));
        }

        $this->Value = $value;
        return $this;
    }

    public function __toString(): string
    {
        return $this->Value;
    }

    public function getCausaExencion(): string
    {
        return $this->CausaExencion;
    }

    public function setCausaExencion(string $causaExencion): self
    {
        if (!preg_match('/^[A-Z]\d{2}$/', $causaExencion)) {
            throw new \InvalidArgumentException('CausaExencion must be a letter followed by two digits');
        }
        $this->CausaExencion = $causaExencion;
        return $this;
    }

    public function getBaseImponible(): float
    {
        return $this->BaseImponible;
    }

    public function setBaseImponible(float $baseImponible): self
    {
        $parts = explode('.', (string)$baseImponible);
        $integerPart = $parts[0];
        $decimalPart = $parts[1] ?? '';

        if (strlen($integerPart) > 12) {
            throw new \InvalidArgumentException('BaseImponible must have at most 12 digits before decimal point');
        }
        if (strlen($decimalPart) > 2) {
            throw new \InvalidArgumentException('BaseImponible must have at most 2 decimal places');
        }

        $this->BaseImponible = $baseImponible;
        return $this;
    }
} 