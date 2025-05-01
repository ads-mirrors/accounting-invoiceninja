<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class Desglose
{
    /** @var array<DetalleDesglose> */
    protected $DetalleDesglose = [];

    /**
     * @return array<DetalleDesglose>
     */
    public function getDetalleDesglose(): array
    {
        return $this->DetalleDesglose;
    }

    public function addDetalleDesglose(DetalleDesglose $detalle): self
    {
        $this->DetalleDesglose[] = $detalle;
        return $this;
    }

    /**
     * @param array<DetalleDesglose> $detalles
     */
    public function setDetalleDesglose(array $detalles): self
    {
        $this->DetalleDesglose = $detalles;
        return $this;
    }
} 