<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class RegistroAnterior extends IDFactura
{
    /** @var string */
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