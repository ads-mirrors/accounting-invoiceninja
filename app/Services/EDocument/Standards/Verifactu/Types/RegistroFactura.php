<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class RegistroFactura
{
    /** @var RegistroAlta */
    #[SerializedName('sum1:RegistroAlta')]
    protected $RegistroAlta;

    /** @var RegistroFacturacionAnulacion|null */
    #[SerializedName('sum1:RegistroAnulacion')]
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