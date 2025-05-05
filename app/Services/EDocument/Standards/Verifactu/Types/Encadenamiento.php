<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Encadenamiento
{
    /** @var RegistroAnterior */
    #[SerializedName('sum1:RegistroAnterior')]
    protected $RegistroAnterior;

    /** @var string */
    #[SerializedName('sum1:PrimerRegistro')]
    protected $PrimerRegistro;

    public function getRegistroAnterior(): RegistroAnterior
    {
        return $this->RegistroAnterior;
    }

    public function setRegistroAnterior(RegistroAnterior $registroAnterior): self
    {
        $this->RegistroAnterior = $registroAnterior;
        return $this;
    }

    public function getPrimerRegistro(): string
    {
        return $this->PrimerRegistro;
    }

    public function setPrimerRegistro(string $primerRegistro): self
    {
        if (strlen($primerRegistro) > 64) {
            throw new \InvalidArgumentException('HuellaRegistroAnterior must not exceed 64 characters');
        }
        $this->PrimerRegistro = $primerRegistro;
        return $this;
    }
} 