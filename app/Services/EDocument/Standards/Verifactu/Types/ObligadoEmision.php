<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * ObligadoEmision represents a required entity with NombreRazon and NIF.
 * Extends PersonaFisicaJuridicaES but enforces both properties to be required at construction time.
 */
class ObligadoEmision extends PersonaFisicaJuridicaES
{
    public function __construct()
    {

    }

    public function setNombreRazon(?string $nombreRazon): self
    {
        if (empty($nombreRazon)) {
            throw new \InvalidArgumentException('NombreRazon is required for ObligadoEmision');
        }
        return parent::setNombreRazon($nombreRazon);
    }

    public function setNIF(string $nif): self
    {
        if (empty($nif)) {
            throw new \InvalidArgumentException('NIF is required for ObligadoEmision');
        }
        return parent::setNIF($nif);
    }
} 