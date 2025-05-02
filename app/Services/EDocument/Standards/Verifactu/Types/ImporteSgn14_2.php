<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use App\Services\EDocument\Standards\Verifactu\Types\Common\TextTypes;
use Symfony\Component\Serializer\Annotation\SerializedName;

class ImporteSgn14_2
{
    use TextTypes;

    /** @var string */
    #[SerializedName('sum1:Value')]
    protected $Value;

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
        $this->validateNumericString($value, 14, 2, 'Amount');
        $this->Value = $value;
        return $this;
    }

    public function __toString(): string
    {
        return $this->Value;
    }
} 