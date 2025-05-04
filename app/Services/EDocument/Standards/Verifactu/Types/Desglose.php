<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Desglose
{
    /** @var DetalleDesglose[] */
    #[SerializedName('sum1:DetalleDesglose')]
    protected $DetalleDesglose = [];

    /**
     * @return DetalleDesglose[]
     */
    public function getDetalleDesglose(): array
    {
        return $this->DetalleDesglose;
    }

    public function addDetalleDesglose(DetalleDesglose $detalleDesglose): self
    {
        $this->DetalleDesglose[] = $detalleDesglose;
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