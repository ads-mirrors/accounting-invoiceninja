<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class RegFactuSistemaFacturacion
{
    /** @var Cabecera */
    protected $cabecera;

    /** @var RegistroFactura */
    protected $registroFactura;

    public function getCabecera(): Cabecera
    {
        return $this->cabecera;
    }

    public function setCabecera(Cabecera $cabecera): self
    {
        $this->cabecera = $cabecera;
        return $this;
    }

    public function getRegistroFactura(): RegistroFactura
    {
        return $this->registroFactura;
    }

    public function setRegistroFactura(RegistroFactura $registroFactura): self
    {
        $this->registroFactura = $registroFactura;
        return $this;
    }
} 