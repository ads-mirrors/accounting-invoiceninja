<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class RegistroAnterior extends IDFactura
{
    /** @var string */
    #[SerializedName('sum1:Huella')]
    protected $Huella;

    public function getHuella(): string
    {
        return $this->Huella;
    }

    public function setHuella(string $huella): self
    {
        $this->Huella = $huella;
        return $this;
    }
} 