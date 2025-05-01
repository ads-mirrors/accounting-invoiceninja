<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class RegistroFactura
{
    /** @var RegistroAlta */
    protected $RegistroAlta;

    /** @var RegistroFacturacionAnulacion|null */
    protected $RegistroAnulacion;

    public function getRegistroAlta(): RegistroAlta
    {
        return $this->RegistroAlta;
    }

    public function setRegistroAlta(RegistroAlta $registroAlta): self
    {
        if ($registroAlta !== null && $this->RegistroAnulacion !== null) {
            throw new \InvalidArgumentException('Cannot set both RegistroAlta and RegistroAnulacion');
        }
        $this->RegistroAlta = $registroAlta;
        return $this;
    }

    public function getRegistroAnulacion(): ?RegistroFacturacionAnulacion
    {
        return $this->RegistroAnulacion;
    }

    public function setRegistroAnulacion(?RegistroFacturacionAnulacion $registroAnulacion): self
    {
        if ($registroAnulacion !== null && $this->RegistroAlta !== null) {
            throw new \InvalidArgumentException('Cannot set both RegistroAlta and RegistroAnulacion');
        }
        $this->RegistroAnulacion = $registroAnulacion;
        return $this;
    }
} 