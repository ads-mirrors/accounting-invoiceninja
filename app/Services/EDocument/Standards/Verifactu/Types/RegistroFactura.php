<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class RegistroFactura
{
    /** @var RegistroAlta */
    protected $registroAlta;

    /** @var RegistroFacturacionAnulacion|null */
    protected $registroAnulacion;

    public function getRegistroAlta(): RegistroAlta
    {
        return $this->registroAlta;
    }

    public function setRegistroAlta(RegistroAlta $registroAlta): self
    {
        if ($registroAlta !== null && $this->registroAnulacion !== null) {
            throw new \InvalidArgumentException('Cannot set both RegistroAlta and RegistroAnulacion');
        }
        $this->registroAlta = $registroAlta;
        return $this;
    }

    public function getRegistroAnulacion(): ?RegistroFacturacionAnulacion
    {
        return $this->registroAnulacion;
    }

    public function setRegistroAnulacion(?RegistroFacturacionAnulacion $registroAnulacion): self
    {
        if ($registroAnulacion !== null && $this->registroAlta !== null) {
            throw new \InvalidArgumentException('Cannot set both RegistroAlta and RegistroAnulacion');
        }
        $this->registroAnulacion = $registroAnulacion;
        return $this;
    }
} 