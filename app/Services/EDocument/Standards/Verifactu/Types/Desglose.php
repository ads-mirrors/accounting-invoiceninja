<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class Desglose
{
    /** @var Detalle[] */
    protected $detalle = [];

    /**
     * @return Detalle[]
     */
    public function getDetalle(): array
    {
        return $this->detalle;
    }

    public function addDetalle(Detalle $detalle): self
    {
        if (count($this->detalle) >= 1000) {
            throw new \RuntimeException('Maximum number of Detalle (1000) exceeded');
        }
        $this->detalle[] = $detalle;
        return $this;
    }

    /**
     * @param Detalle[] $detalle
     */
    public function setDetalle(array $detalle): self
    {
        if (count($detalle) > 1000) {
            throw new \RuntimeException('Maximum number of Detalle (1000) exceeded');
        }
        $this->detalle = $detalle;
        return $this;
    }
} 