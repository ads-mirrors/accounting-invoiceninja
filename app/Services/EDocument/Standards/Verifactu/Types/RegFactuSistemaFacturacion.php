<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class RegFactuSistemaFacturacion
{
    /** @var Cabecera */
    #[SerializedName('sum:Cabecera')]
    protected $Cabecera;

    /** @var RegistroFactura */
    #[SerializedName('sum:RegistroFactura')]
    protected $RegistroFactura;

    public function getCabecera(): Cabecera
    {
        return $this->Cabecera;
    }

    public function setCabecera(Cabecera $cabecera): self
    {
        $this->Cabecera = $cabecera;
        return $this;
    }

    public function getRegistroFactura(): RegistroFactura
    {
        return $this->RegistroFactura;
    }

    public function setRegistroFactura(RegistroFactura $registroFactura): self
    {
        $this->RegistroFactura = $registroFactura;
        return $this;
    }
} 