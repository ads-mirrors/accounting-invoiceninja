<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class RegistroAnterior
{
    /** @var string */
    #[SerializedName('sum1:IDEmisorFactura')]
    protected $IDEmisorFactura;

    /** @var string */
    #[SerializedName('sum1:NumSerieFactura')]
    protected $NumSerieFactura;

    /** @var string */
    #[SerializedName('sum1:FechaExpedicionFactura')]
    protected $FechaExpedicionFactura;

    /** @var string */
    #[SerializedName('sum1:Huella')]
    protected $Huella;

    public function getIDEmisorFactura(): string
    {
        return $this->IDEmisorFactura;
    }

    public function setIDEmisorFactura(string $IDEmisorFactura): self
    {
        $this->IDEmisorFactura = $IDEmisorFactura;
        return $this;
    }

    public function getNumSerieFactura(): string
    {
        return $this->NumSerieFactura;
    }

    public function setNumSerieFactura(string $NumSerieFactura): self
    {
        $this->NumSerieFactura = $NumSerieFactura;
        return $this;
    }

    public function getFechaExpedicionFactura(): string
    {
        return $this->FechaExpedicionFactura;
    }

    public function setFechaExpedicionFactura(string $FechaExpedicionFactura): self
    {
        $this->FechaExpedicionFactura = $FechaExpedicionFactura;
        return $this;
    }

    public function getHuella(): string
    {
        return $this->Huella;
    }

    public function setHuella(string $Huella): self
    {
        $this->Huella = $Huella;
        return $this;
    }
} 