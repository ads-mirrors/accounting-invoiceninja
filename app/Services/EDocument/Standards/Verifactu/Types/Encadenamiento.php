<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Encadenamiento
{
    /** @var IDFacturaAR */
    #[SerializedName('sum1:RegistroAnterior')]
    protected $RegistroAnterior;

    /** @var string */
    #[SerializedName('sum1:HuellaRegistroAnterior')]
    protected $HuellaRegistroAnterior;

    public function getRegistroAnterior(): IDFacturaAR
    {
        return $this->RegistroAnterior;
    }

    public function setRegistroAnterior(IDFacturaAR $registroAnterior): self
    {
        $this->RegistroAnterior = $registroAnterior;
        return $this;
    }

    public function getHuellaRegistroAnterior(): string
    {
        return $this->HuellaRegistroAnterior;
    }

    public function setHuellaRegistroAnterior(string $huellaRegistroAnterior): self
    {
        if (strlen($huellaRegistroAnterior) > 64) {
            throw new \InvalidArgumentException('HuellaRegistroAnterior must not exceed 64 characters');
        }
        $this->HuellaRegistroAnterior = $huellaRegistroAnterior;
        return $this;
    }
} 