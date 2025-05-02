<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class SoapBody
{
    /** @var RegFactuSistemaFacturacion */
    #[SerializedName('sum:RegFactuSistemaFacturacion')]
    protected $RegFactuSistemaFacturacion;

    public function getRegFactuSistemaFacturacion(): RegFactuSistemaFacturacion
    {
        return $this->RegFactuSistemaFacturacion;
    }

    public function setRegFactuSistemaFacturacion(RegFactuSistemaFacturacion $regFactuSistemaFacturacion): self
    {
        $this->RegFactuSistemaFacturacion = $regFactuSistemaFacturacion;
        return $this;
    }
}
