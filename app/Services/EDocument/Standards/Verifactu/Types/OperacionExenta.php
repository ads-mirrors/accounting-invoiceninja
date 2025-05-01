<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class OperacionExent
{
    public const E1 = 'E1'; // EXENTA por Art. 20
    public const E2 = 'E2'; // EXENTA por Art. 21
    public const E3 = 'E3'; // EXENTA por Art. 22
    public const E4 = 'E4'; // EXENTA por Art. 24
    public const E5 = 'E5'; // EXENTA por Art. 25
    public const E6 = 'E6'; // EXENTA por otros

    /** @var string */
    protected $value;

    public function __construct(string $value)
    {
        $this->setValue($value);
    }

    public function getValue(): string
    {
        return $this->value;
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

        $this->value = $value;
        return $this;
    }

    public function __toString(): string
    {
        return $this->value;
    }
} 