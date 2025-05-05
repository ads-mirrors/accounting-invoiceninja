<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * ObligadoEmision represents a required entity with NombreRazon and NIF.
 * Extends PersonaFisicaJuridicaES but enforces both properties to be required at construction time.
 */
class ObligadoEmision
{
    /** @var string */
    #[SerializedName('sum1:NombreRazon')]
    protected $NombreRazon;

    /** @var string|null */
    #[SerializedName('sum1:NIF')]
    protected $NIF;
    
    public function setNombreRazon(?string $nombreRazon): self
    {
        if (empty($nombreRazon)) {
            throw new \InvalidArgumentException('NombreRazon is required for ObligadoEmision');
        }

        $this->NombreRazon = $nombreRazon;
     
        return $this;
    }

    public function getNombreRazon(): string
    {
        return $this->NombreRazon;
    }

    public function getNIF(): string
    {
        return $this->NIF;
    }

    public function setNIF(string $nif): self
    {
        if (empty($nif)) {
            throw new \InvalidArgumentException('NIF is required for ObligadoEmision');
        }
        
        $this->NIF = $nif;
        return $this;
    }
} 